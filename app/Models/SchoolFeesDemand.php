<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolFeesDemand extends Model
{
    use HasFactory;

    public function getClassesAttribute($value)
    {
        if ($value == null || strlen($value) < 3) {
            return [];
        }
        return json_decode($value);
    }

    public function setClassesAttribute($value)
    {
        if ($value != null && is_array($value)) {
            $this->attributes['classes'] = json_encode($value);
        } else {
            $this->attributes['classes'] = '[]';
        }
    }

    /** Default notice body used when a demand is raised from commitments. */
    public const COMMITMENT_TEMPLATE = <<<'HTML'
<p>Dear Parent/Guardian of <b>[STUDENT_NAME]</b> ([STUDENT_CLASS]),</p>
<p>[COMMITMENT_NOTE]</p>
<p>Our records show an outstanding school fees balance of <b>UGX [BALANCE_AMOUNT]</b>
on School Pay code <b>[SCHOOL_PAY_CODE]</b>.</p>
<p>We kindly request that this balance be settled without further delay. If payment
has already been made, please ignore this notice and share the payment reference
with the Bursar's office.</p>
HTML;

    /**
     * Raise one school-fees demand covering the given commitment records.
     *
     * The demand targets those students explicitly, so it prints through the
     * existing Generate Demand Notices flow unchanged. `classes` is filled with
     * the students' current classes for display only — selection is by id.
     *
     * @param  \Illuminate\Support\Collection|array  $commitments
     */
    public static function createFromCommitments($commitments, array $options = []): self
    {
        $commitments = collect($commitments)->filter(function ($c) {
            return $c && (int) $c->student_id > 0;
        })->values();

        if ($commitments->isEmpty()) {
            throw new \Exception('No commitment records with a linked student were supplied.');
        }

        $enterpriseId = (int) ($options['enterprise_id'] ?? $commitments->first()->enterprise_id);
        $studentIds   = $commitments->pluck('student_id')->map('intval')->unique()->values();

        $classIds = User::whereIn('id', $studentIds)
            ->pluck('current_class_id')
            ->filter()
            ->map('intval')
            ->unique()
            ->values()
            ->toArray();

        $demand = new self();
        $demand->enterprise_id         = $enterpriseId;
        $demand->description           = $options['description']
            ?? 'Fees demand from parent commitments - ' . date('d M Y H:i');
        // balance < 0 keeps every targeted student who still owes anything, and
        // silently drops any who cleared between the commitment and this run.
        $demand->amount                = 0;
        $demand->direction             = '<';
        $demand->target_type           = 'ALL';
        $demand->has_specific_students = 'Yes';
        $demand->target_students       = $studentIds->toArray();
        $demand->classes               = $classIds;
        $demand->message_1             = $options['message_1'] ?? self::COMMITMENT_TEMPLATE;
        $demand->message_2             = $options['message_2'] ?? null;
        $demand->message_4             = $options['due_date'] ?? null;
        $demand->has_range             = 'No';
        $demand->include_student_photos = $options['include_student_photos'] ?? 'No';
        $demand->sms_sent              = 'No';
        $demand->pdf_generated         = 'No';
        $demand->source                = self::SOURCE_COMMITMENT;
        $demand->commitment_ids        = $commitments->pluck('id')->map('intval')->toArray();
        $demand->save();

        return $demand;
    }

    /** Marks a demand that was generated from parent commitment records. */
    public const SOURCE_COMMITMENT = 'PARENT_COMMITMENT';

    public function getCommitmentIdsAttribute($value)
    {
        if ($value === null || strlen($value) < 1) {
            return [];
        }
        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    public function setCommitmentIdsAttribute($value)
    {
        $this->attributes['commitment_ids'] = json_encode(
            is_array($value) ? array_values(array_unique(array_map('intval', $value))) : []
        );
    }

    /** True when this demand came from the parent-commitment module. */
    public function isFromCommitments(): bool
    {
        return $this->source === self::SOURCE_COMMITMENT;
    }

    /**
     * The commitment record behind this demand for one student.
     *
     * Prefers a record named on the demand, so a notice always quotes the
     * commitment it was generated from rather than one the parent made later.
     * Falls back to the student's most recent commitment.
     */
    public function commitmentForStudent($studentId): ?ParentCommitmentRecord
    {
        $studentId = (int) $studentId;
        if ($studentId <= 0) {
            return null;
        }

        $ids = $this->commitment_ids;
        if (!empty($ids)) {
            $match = ParentCommitmentRecord::withoutGlobalScopes()
                ->whereIn('id', $ids)
                ->where('student_id', $studentId)
                ->orderByDesc('commitment_date')
                ->first();
            if ($match) {
                return $match;
            }
        }

        return ParentCommitmentRecord::withoutGlobalScopes()
            ->where('enterprise_id', $this->enterprise_id)
            ->where('student_id', $studentId)
            ->orderByDesc('commitment_date')
            ->first();
    }

    /**
     * Placeholder values describing the parent's commitment.
     *
     * Every key is returned even when there is no commitment on file, so a
     * template can never print a raw "[COMMITMENT_DATE]" back at a parent.
     */
    public function commitmentPlaceholders($studentId): array
    {
        $blank = [
            '[PARENT_NAME]'        => '',
            '[PARENT_CONTACT]'     => '',
            '[COMMITMENT_DATE]'    => '',
            '[COMMITMENT_MADE_ON]' => '',
            '[PROMISE_STATUS]'     => '',
            '[COMMITTED_BALANCE]'  => '',
            '[DAYS_OVERDUE]'       => '',
            '[COMMITMENT_NOTE]'    => '',
        ];

        $c = $this->commitmentForStudent($studentId);
        if ($c === null) {
            return $blank;
        }

        $due       = $c->commitment_date ? \Carbon\Carbon::parse($c->commitment_date) : null;
        $madeOn    = $c->created_at ? \Carbon\Carbon::parse($c->created_at) : null;
        $daysLate  = ($due && $due->isPast()) ? $due->diffInDays(\Carbon\Carbon::now()) : 0;
        $dueText   = $due ? $due->format('d M Y') : '';
        $madeText  = $madeOn ? $madeOn->format('d M Y') : '';
        $committed = number_format(abs((float) $c->outstanding_balance));

        $note = '';
        if ($dueText !== '') {
            $note = 'On ' . ($madeText !== '' ? $madeText : $dueText)
                . ', ' . ($c->parent_name ?: 'the parent/guardian')
                . ' committed to clear an outstanding balance of UGX ' . $committed
                . ' by ' . $dueText . '.';
            if ($daysLate > 0) {
                $note .= ' That date passed ' . $daysLate . ' day' . ($daysLate === 1 ? '' : 's')
                    . ' ago and the balance remains unsettled.';
            }
        }

        return [
            '[PARENT_NAME]'        => (string) ($c->parent_name ?: ''),
            '[PARENT_CONTACT]'     => (string) ($c->parent_contact ?: ''),
            '[COMMITMENT_DATE]'    => $dueText,
            '[COMMITMENT_MADE_ON]' => $madeText,
            '[PROMISE_STATUS]'     => (string) ($c->promise_status ?: ''),
            '[COMMITTED_BALANCE]'  => $committed,
            '[DAYS_OVERDUE]'       => (string) $daysLate,
            '[COMMITMENT_NOTE]'    => $note,
        ];
    }

    public static function get_demand_message($demand, $account)
    {
        $school_pay_payment_code = "";
        if ($account->owner->school_pay_payment_code != null && strlen($account->owner->school_pay_payment_code) > 0) {
            $school_pay_payment_code = $account->owner->school_pay_payment_code;
        }
        $content = $demand->message_1;
        if ($account->owner != null) {
            $content = str_replace("[STUDENT_NAME]", $account->owner->name, $content);
        }
        // abs(): balances are stored negative for debt, and every template reads
        // "outstanding balance of UGX [BALANCE_AMOUNT]", so the raw value printed
        // "UGX -35,000". Same convention the bulk-messaging module already uses.
        $content = str_replace("[BALANCE_AMOUNT]", number_format(abs((float) $account->balance)), $content);
        $content = str_replace("[SCHOOL_PAY_CODE]", $school_pay_payment_code, $content);
        if ($account->owner->current_class != null) {
            $content = str_replace("[STUDENT_CLASS]", $account->owner->current_class->name_text, $content);
        }

        // Commitment placeholders. Resolved for every demand, not just
        // commitment-generated ones, so a template that mentions a promise still
        // fills in correctly if the parent has one on file.
        if ($demand instanceof self && $account->owner != null) {
            foreach ($demand->commitmentPlaceholders($account->owner->id) as $token => $value) {
                $content = str_replace($token, $value, $content);
            }
        }

        return $content;
    }

    /**
     * Demand records for a demand that names its students explicitly.
     *
     * Each student appears exactly once, grouped under the class they are in
     * today. The balance condition is applied exactly as for class-wide demands;
     * no residence or status filter is added, so the population is unchanged
     * from before — only the duplication is gone.
     */
    private function get_specific_student_records(int $balance): array
    {
        $ids = [];
        foreach ((array) $this->target_students as $student_id) {
            $id = (int) $student_id;
            if ($id > 0) {
                $ids[$id] = $id; // keyed, so a repeated id cannot double a notice
            }
        }
        if (empty($ids)) {
            return [];
        }

        $accounts = Account::where(['enterprise_id' => $this->enterprise_id])
            ->whereIn('administrator_id', array_values($ids))
            ->where('balance', $this->direction, $balance)
            ->orderBy('balance', 'desc')
            ->get();

        $classOf = User::whereIn('id', array_values($ids))
            ->pluck('current_class_id', 'id');

        $recs = [];
        foreach ($accounts as $account) {
            $classId = (int) ($classOf[$account->administrator_id] ?? 0);
            $recs[$classId][] = $account;
        }

        return $recs;
    }

    function get_demand_records()
    {

        $balance =  (int)($this->amount);

        // Specific students are chosen by id, so $this->classes plays no part in
        // selecting them. Resolving them once — instead of re-resolving the same
        // full list inside the per-class loop below — is what stops each notice
        // printing once per class (demand #61 printed 18 students as 36 notices).
        // Grouping is by the student's CURRENT class, because targeted students
        // are often promoted out of the classes stored on the demand.
        if ($this->has_specific_students == 'Yes') {
            return $this->get_specific_student_records($balance);
        }

        $recs = [];
        $target_type = $this->target_type;
        foreach ($this->classes as $key => $class) {
            $conds = [
                'enterprise_id' => $this->enterprise_id,
                'user_type' => 'student',
                'status' => 1,
                'current_class_id' => $class
            ];
            if ($target_type == 'ALL') {
            } else if ($target_type == 'DAY_SCHOLAR') {
                $conds['residence'] = $target_type;
            } else if ($target_type == 'BOARDER') {
                $conds['residence'] = $target_type;
            } else {
                throw new \Exception('Invalid target type');
            }

            $ids = [];
            if ($this->has_specific_students == 'Yes') {
                foreach ($this->target_students as $key => $student_id) {
                    $ids[] = (int)($student_id);
                }
            } else {
                $ids = User::where($conds)
                    ->get()
                    ->pluck('id')
                    ->toArray();
            }

            $accounts = Account::where([
                'enterprise_id' => $this->enterprise_id,
            ])
                ->whereIn('administrator_id', $ids)
                ->where('balance', $this->direction, $balance)
                ->orderBy('balance', 'desc')

                ->get();
            $recs[$class] = $accounts;
        }
        return $recs;
    }

    function get_meal_card_records()
    {

        $balance = (int)($this->amount);
        $conds = [
            'enterprise_id' => $this->enterprise_id,
            'user_type' => 'student',
            'status' => 1,
        ];
        if ($this->target_type == 'ALL') {
        } else if ($this->target_type == 'DAY_SCHOLAR') {
            $conds['residence'] = $this->target_type;
        } else if ($this->target_type == 'BOARDER') {
            $conds['residence'] = $this->target_type;
        } else {
            throw new \Exception('Invalid target type');
        }

        $all_ids = [];
        $recs = [];
        $done_ids = [];
        foreach ($this->classes as $key => $class) {
            $conds['current_class_id'] = $class;

            $ids = [];
            if ($this->has_specific_students == 'Yes') {
                foreach ($this->target_students as $key => $student_id) {
                    $ids[] = (int)($student_id);
                }
            } else {
                $ids = User::where($conds)
                    ->get()
                    ->pluck('id')
                    ->toArray();
            }
            //check if ids are already in $done_ids and remove them
            $ids = array_diff($ids, $done_ids);
            //$done_ids
            $done_ids = array_merge($done_ids, $ids);


        $accounts = Account::where([
                'enterprise_id' => $this->enterprise_id,
            ])
                ->whereIn('administrator_id', $ids)
                ->where('balance', $this->direction, $balance)
                ->orderBy('balance', 'desc')
                ->get();
            $recs[$class] = $accounts;
            $all_ids = array_merge($all_ids, $ids);
        }

        $dup_ids = [];
        $unique_ids = [];
        foreach ($all_ids as $key => $value) {
            if (in_array($value, $unique_ids)) {
                $dup_ids[] = $value;
            } else {
                $unique_ids[] = $value;
            }
        }
        //
        // dd($all_ids);
        /* implement  */
        return $recs;
    }


    //getter for target_students
    public function getTargetStudentsAttribute($value)
    {
        if ($value == null || strlen($value) < 3) {
            return [];
        }
        return json_decode($value);
    }

    //setter for target_students
    public function setTargetStudentsAttribute($value)
    {
        if ($value != null && is_array($value)) {
            $this->attributes['target_students'] = json_encode($value);
        } else {
            $this->attributes['target_students'] = '[]';
        }
        $this->attributes['target_students'] = json_encode($value);
    }
}
