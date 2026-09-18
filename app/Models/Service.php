<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'enterprise_id',
        'name',
        'fee',
        'description',
        'is_compulsory',
        'service_category_id',
        'is_default',
    ];

    public function service_category()
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public static function boot()
    {
        parent::boot();
        self::updated(function ($m) {
            Service::update_fees($m);
        });
        self::created(function ($m) {
            Service::update_fees($m);
        });

        self::deleting(function ($m) {
            if ($m->is_default === 'Yes') {
                throw new Exception("The default service cannot be deleted.");
            }

            $defaultService = Service::where([
                'enterprise_id' => $m->enterprise_id,
                'is_default' => 'Yes',
            ])->first();

            if (!$defaultService) {
                throw new Exception("No default service found. Cannot reassign subscriptions.");
            }

            // Reassign all subscriptions from this service to the default service
            ServiceSubscription::where('service_id', $m->id)
                ->update(['service_id' => $defaultService->id]);

            // Reassign batch service subscriptions too
            BatchServiceSubscription::where('service_id', $m->id)
                ->update(['service_id' => $defaultService->id]);
        });
    }

    /**
     * Get or create the default service for an enterprise.
     */
    public static function ensureDefaultService($enterprise_id)
    {
        $default = self::where([
            'enterprise_id' => $enterprise_id,
            'is_default' => 'Yes',
        ])->first();

        if ($default) {
            return $default;
        }

        // Find or create an "Others" service category for this enterprise
        $category = ServiceCategory::where('enterprise_id', $enterprise_id)->first();
        if (!$category) {
            $category = ServiceCategory::create([
                'enterprise_id' => $enterprise_id,
                'name' => 'Others',
            ]);
        }

        $default = new self();
        $default->enterprise_id = $enterprise_id;
        $default->name = 'Default Service';
        $default->fee = 0;
        $default->description = 'Default service. Subscriptions from deleted services are reassigned here.';
        $default->service_category_id = $category->id;
        $default->is_default = 'Yes';
        $default->save();

        return $default;
    }



    /**
     * Bills every subscription of this service that has not been billed yet.
     *
     * Kept for the service create/update path, where a fee change legitimately
     * needs to sweep the whole service. Do NOT call this after creating a single
     * subscription — use bill_subscription() instead, otherwise every insert
     * re-walks all subscriptions of the service and the cost becomes quadratic.
     */
    public static function update_fees($m)
    {
        if ($m == null) {
            return 0;
        }

        $billed = 0;
        foreach ($m->subs as $s) {
            if (self::bill_subscription($m, $s)) {
                $billed++;
            }
        }

        return $billed;
    }

    /**
     * Posts the fee transaction for exactly one subscription.
     *
     * Idempotent: a FeeDepositConfirmation row is the ledger's "already billed"
     * marker for a subscription, so calling this twice never double-charges.
     *
     * Returns true when a charge was posted, false when it was already billed.
     */
    public static function bill_subscription($service, $sub)
    {
        if ($service == null || $sub == null) {
            return false;
        }

        $subscriptionId = (int) $sub->id;
        $subscriberId   = (int) $sub->administrator_id;
        if ($subscriptionId < 1 || $subscriberId < 1) {
            return false;
        }

        $alreadyBilled = FeeDepositConfirmation::where([
            'fee_id'           => $subscriptionId,
            'administrator_id' => $subscriberId,
        ])->first();
        if ($alreadyBilled != null) {
            return false;
        }

        $ent = Enterprise::find($service->enterprise_id);
        if ($ent == null) {
            throw new Exception("Enterprise #{$service->enterprise_id} was not found for service {$service->name}.", 1);
        }

        $admin = Administrator::find($subscriberId);
        if ($admin == null) {
            throw new Exception("Subscriber #{$subscriberId} was not found.", 1);
        }

        $account = $admin->account;
        if ($account == null) {
            $account = Account::create($subscriberId);
        }
        if ($account == null) {
            throw new Exception("Financial account for {$admin->name} could not be created.", 1);
        }

        $by = Auth::user();
        if ($by == null) {
            $by = Admin::user();
        }
        if ($by == null) {
            throw new Exception("Cannot bill {$admin->name}: no authenticated user to attribute the charge to.", 1);
        }

        $quantity = (int) $sub->quantity;
        if ($quantity < 1) {
            $quantity = 1;
        }

        $trans                          = new Transaction();
        $trans->enterprise_id           = $ent->id;
        $trans->account_id              = $account->id;
        $trans->created_by_id           = $by->id;
        $trans->school_pay_transporter_id = '-';
        $trans->amount                  = (-1) * abs($service->fee) * $quantity;
        $trans->payment_date            = Carbon::now()->toDateTimeString();
        $trans->is_contra_entry         = false;
        $trans->type                    = 'FEES_BILL';
        $trans->is_service              = 'Yes';
        $trans->service_id              = $service->id;
        $trans->contra_entry_account_id = 0;
        $trans->description             = "Debited UGX " . number_format((int) $trans->amount) . " for {$service->name} service.";

        // The charge belongs to the term the subscription is FOR, not to whichever
        // term happens to be active when the batch is run. Subscriptions are
        // routinely created ahead of the term they bill.
        $termId         = (int) $sub->due_term_id;
        $academicYearId = (int) $sub->due_academic_year_id;

        if ($termId > 0 && $academicYearId < 1) {
            $dueTerm = Term::find($termId);
            if ($dueTerm != null) {
                $academicYearId = (int) $dueTerm->academic_year_id;
            }
        }

        if ($termId < 1) {
            $activeTerm = $ent->active_term();
            if ($activeTerm != null) {
                $termId         = (int) $activeTerm->id;
                $academicYearId = (int) $activeTerm->academic_year_id;
            }
        }

        if ($termId > 0) {
            $trans->term_id = $termId;
        }
        if ($academicYearId > 0) {
            $trans->academic_year_id = $academicYearId;
        }

        // The charge and its "already billed" marker must land together. Saving
        // the marker first (as this used to) meant a failing transaction left a
        // marker behind: the subscription then looked billed for ever, blocking
        // every retry, while the student was never actually charged. Six real
        // cases of exactly that were found on service 218 in Sep 2026.
        DB::transaction(function () use ($trans, $ent, $subscriptionId, $subscriberId) {
            $trans->save();

            $fee_dep                   = new FeeDepositConfirmation();
            $fee_dep->enterprise_id    = $ent->id;
            $fee_dep->fee_id           = $subscriptionId;
            $fee_dep->administrator_id = $subscriberId;
            $fee_dep->save();
        });

        return true;
    }
    public function subs()
    {
        return $this->hasMany(ServiceSubscription::class);
    }

    //appends name_text
    public function getNameTextAttribute()
    {
        return $this->name . ' - UGX ' . number_format($this->fee);
    }


    //create service if not exists
    public static function createIfNotExists(array $data)
    {
        if (
            !isset($data['name']) ||
            !isset($data['fee']) ||
            !isset($data['service_category_id']) ||
            !isset($data['enterprise_id'])
        ) {
            throw new Exception("Required fields are missing.");
        }

        $service = self::where([
            'name' => $data['name'],
            'fee' => $data['fee'],
            'enterprise_id' => $data['enterprise_id'],
        ])->first();

        if (!$service) {
            $service = new self();
            $service->name = $data['name'];
            $service->fee = $data['fee'];
            $service->service_category_id = $data['service_category_id'];
            $service->enterprise_id = $data['enterprise_id'];
            if (isset($data['description'])) {
                $service->description = $data['description'];
            }
            $service->save();
        }

        return $service;
    }


    //applicable_to_courses setter to join courses
    public function setApplicableToCoursesAttribute($value)
    {
        try {
            if (is_array($value)) {
                $this->attributes['applicable_to_courses'] = json_encode($value);
            } else {
                $this->attributes['applicable_to_courses'] = $value;
            }
        } catch (\Exception $e) {
            // Handle or log the exception as needed
            $this->attributes['applicable_to_courses'] = null;
        }
    }

    //getter for applicable_to_coursesapplicable_to_courses
    public function getApplicableToCoursesAttribute()
    {
        try {
            if (isset($this->attributes['applicable_to_courses'])) {
                return json_decode($this->attributes['applicable_to_courses'], true);
            }
            return [];
        } catch (\Exception $e) {
            // Handle or log the exception as needed
            return [];
        }
    }

    //getter for applicable_to_semesters
    public function getApplicableToSemestersAttribute()
    {
        try {
            if (isset($this->attributes['applicable_to_semesters'])) {
                return json_decode($this->attributes['applicable_to_semesters'], true);
            }
            return [];
        } catch (\Exception $e) {
            // Handle or log the exception as needed
            return [];
        }
    }

    //applicable_to_semesters setter to join semesters
    public function setApplicableToSemestersAttribute($value)
    {
        try {
            if (is_array($value)) {
                $this->attributes['applicable_to_semesters'] = json_encode($value);
            } else {
                $this->attributes['applicable_to_semesters'] = $value;
            }
        } catch (\Exception $e) {
            // Handle or log the exception as needed
            $this->attributes['applicable_to_semesters'] = null;
        }
    }

    // items_to_be_offered getter to decode JSON
    public function getItemsToBeOfferedAttribute($value)
    {
        try {
            if (!empty($value)) {
                $decoded = json_decode($value, true);
                return is_array($decoded) ? $decoded : [];
            }
            return [];
        } catch (\Exception $e) {
            return [];
        }
    }

    // items_to_be_offered setter to encode array as JSON
    public function setItemsToBeOfferedAttribute($value)
    {
        try {
            if (is_array($value)) {
                $this->attributes['items_to_be_offered'] = json_encode($value);
            } elseif (is_string($value) && !empty($value)) {
                $this->attributes['items_to_be_offered'] = $value;
            } else {
                $this->attributes['items_to_be_offered'] = null;
            }
        } catch (\Exception $e) {
            $this->attributes['items_to_be_offered'] = null;
        }
    }
}
