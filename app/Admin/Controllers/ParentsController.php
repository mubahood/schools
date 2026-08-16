<?php

namespace App\Admin\Controllers;

use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use Encore\Admin\Widgets\Tab;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\MessageBag;

class ParentsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Parents';

    /**
     * Columns that may hold a guardian's contact number, in the order the grid's
     * getParentPhonNumber() resolves them. Keep these in sync — if they diverge,
     * the list shows a number the edit form cannot see.
     */
    public const PHONE_FALLBACK_COLUMNS = [
        'emergency_person_phone',
        'phone_number_2',
        'father_phone',
        'mother_phone',
        'spouse_phone',
    ];

    /**
     * Placeholder junk that earlier imports wrote into phone/username columns.
     * These must never be treated as a real number.
     */
    public const PHONE_JUNK = ['+256(not set)', '(not set)', 'not set', 'null', 'n/a', '-', '+256'];

    /**
     * True when $value looks like a usable Ugandan mobile number.
     *
     * Deliberately local to this controller: Utils::phone_number_is_valid()
     * currently returns true for everything, and tightening it globally would
     * block saves across students/employees too.
     */
    public static function isRealPhone($value): bool
    {
        $value = trim((string) $value);
        if ($value === '') {
            return false;
        }
        if (in_array(strtolower($value), array_map('strtolower', self::PHONE_JUNK), true)) {
            return false;
        }
        $prepared = Utils::prepare_phone_number($value);

        // Must be +256 followed by exactly 9 digits, and nothing else.
        return (bool) preg_match('/^\+256\d{9}$/', $prepared);
    }

    /**
     * Resolve a guardian's real contact number from whichever column holds it.
     * Returns null when the record genuinely has no usable number.
     */
    public static function resolvePhone($model): ?string
    {
        if (self::isRealPhone($model->phone_number_1 ?? null)) {
            return Utils::prepare_phone_number($model->phone_number_1);
        }
        foreach (self::PHONE_FALLBACK_COLUMNS as $col) {
            if (self::isRealPhone($model->$col ?? null)) {
                return Utils::prepare_phone_number($model->$col);
            }
        }
        return null;
    }

    /**
     * Copy a guardian's number into phone_number_1 before the edit form renders.
     *
     * The grid renders getParentPhonNumber(), which falls back across several
     * columns, while the form binds the raw phone_number_1 column. Without this
     * the user sees a number in the list, opens Edit, and finds a blank required
     * field — the exact bug reported from the field.
     */
    protected function backfillGuardianPhone($id): void
    {
        $parent = Administrator::find($id);
        if ($parent === null) {
            return;
        }
        if (self::isRealPhone($parent->phone_number_1)) {
            return; // already good, leave it alone
        }
        $resolved = self::resolvePhone($parent);
        if ($resolved === null) {
            return; // nothing to copy; the form will legitimately ask for one
        }
        // Write directly: avoids firing model events / touching timestamps.
        DB::table('admin_users')->where('id', $parent->id)->update(['phone_number_1' => $resolved]);
    }

    public function edit($id, Content $content)
    {
        $this->backfillGuardianPhone($id);

        return parent::edit($id, $content);
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Administrator());
        $grid->actions(function ($actions) {
            $actions->disableDelete();
        });

        $grid->export(function ($export) {
            $export->except(['kids', 'children']);
        });


        /*         $ git add  .git/MERGE_MSG -f


        UW PICO 5.09                 File: /home4/schooics/public_html/.git/MERGE_MSG                 Modified   */
        $grid->model()
            ->orderBy('id', 'Desc')
            ->where([
                'enterprise_id' => Admin::user()->enterprise_id,
                'user_type' => 'parent'
            ]);
        $grid->actions(function ($actions) {
            //$actions->disableView();
        });


        $grid->filter(function ($filter) {

            $roleModel = config('admin.database.roles_model');
            /*  $filter->equal('main_role_id', 'Filter by role')
                ->select($roleModel::where('slug', '!=', 'super-admin')
                    ->where('slug', '!=', 'student')
                    ->get()
                    ->pluck('name', 'id')); 
  */
        });


        /*         $grid->disableExport();
        $grid->disableCreateButton(); */
        $grid->quickSearch('name')->placeholder('Search by name');
        $grid->disableBatchActions();
        $grid->column('id', __('Id'))->sortable();
        $grid->column('name', __('Name'))->sortable();
        $grid->column('children', __('No. of Children'))
            ->display(function ($x) {
                if ($this->kids == null) {
                    return '-';
                }
                $txt = '<a href="' . admin_url('students/?parent_id=' . $this->id) . '" title="View children" ><b>' . count($this->kids) . "</b></a>";

                return $txt;
            });
        $grid->column('kids', __('Lis of Children'))
            ->display(function ($x) {
                if ($this->kids == null) {
                    return '-';
                }
                $txt = "";
                $isFirst = true;
                foreach ($this->kids as $key => $kid) {
                    if (!$isFirst) {
                        $txt .= ', ';
                    } else {
                        $isFirst = false;
                    }
                    $txt .= '<a href="' . admin_url('students/' . $kid->id) . '" title="' . $kid->name . '" >' . $kid->name . "</a>";
                }
                return $txt;
            });
        $grid->column('roles', 'Roles')->pluck('name')->label()->hide();
        $grid->column('phone_number_1', __('Phone number'))
            ->display(function ($x) {
                $phone_number = $this->getParentPhonNumber();
                if ($phone_number == null) {
                    return "-";
                }
                return $phone_number;
            })
            ->sortable();
        $grid->column('current_address', __('Address'));
        $grid->column('email', __('Email'));
        $grid->column('sex', __('Gender'))->hide();
        $grid->column('religion', __('Religion'))->hide();
        $grid->column('spouse_name', __('Spouse name'))->hide();
        $grid->column('spouse_phone', __('Spouse phone'))->hide();
        $grid->column('father_name')->hide();
        $grid->column('father_phone')->hide();
        $grid->column('mother_name')->hide();
        $grid->column('mother_phone')->hide();
        $grid->column('languages')->hide();
        $grid->column('emergency_person_name')->hide();
        $grid->column('emergency_person_phone')->hide();
        $grid->column('national_id_number', 'N.I.N')->hide();
        $grid->column('passport_number')->hide();
        $grid->column('tin', 'TIN')->hide();
        $grid->column('nssf_number')->hide();
        $grid->column('bank_name')->hide();
        $grid->column('bank_account_number')->hide();
        $grid->column('primary_school_name')->hide();
        $grid->column('primary_school_year_graduated')->hide();
        $grid->column('seconday_school_name')->hide();
        $grid->column('seconday_school_year_graduated')->hide();
        $grid->column('high_school_name')->hide();
        $grid->column('high_school_year_graduated')->hide();
        $grid->column('degree_university_name')->hide();
        $grid->column('degree_university_year_graduated')->hide();
        $grid->column('masters_university_name')->hide();
        $grid->column('masters_university_year_graduated')->hide();
        $grid->column('phd_university_name')->hide();
        $grid->column('phd_university_year_graduated')->hide();
        $grid->column('password')->editable();

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {

        $u = Administrator::findOrFail($id);
        $tab = new Tab();
        $tab->add('Bio', view('admin.dashboard.show-user-profile-bio', [
            'u' => $u
        ]));
        return $tab;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $u = Admin::user();
        $form = new Form(new Administrator());
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });
        $form->divider('Personal Information');

        $u = Admin::user();
        $form->hidden('enterprise_id')->rules('required')->default($u->enterprise_id)
            ->value($u->enterprise_id);

        $form->hidden('user_type')->default('parent')->value('parent');

        $form->text('first_name')->rules('required');
        $form->text('last_name')->rules('required');

        // NOT required: 7,842 of 7,980 existing parents have no gender recorded.
        // Making it mandatory made almost every parent record impossible to edit.
        $form->select('sex', 'Gender')
            ->options(['Male' => 'Male', 'Female' => 'Female'])
            ->help('Optional.');

        $form->text('current_address', 'Address');

        $form->text('phone_number_1', 'Mobile phone number')
            ->rules('required')
            ->help('This is the number the parent uses to log in. Format: 0772123456 or +256772123456.');

        $form->text('phone_number_2', 'Home phone number');
        $form->text('nationality');
        $form->text('religion');

        // ── Guardian / next-of-kin details ───────────────────────────────
        // These columns already hold data for thousands of parents but were
        // never shown on this form, so staff could not see or correct them.
        $form->divider('Guardian & Next of Kin');
        $form->text('spouse_name', 'Spouse name');
        $form->text('spouse_phone', 'Spouse phone');
        $form->text('father_name', 'Father name');
        $form->text('father_phone', 'Father phone');
        $form->text('mother_name', 'Mother name');
        $form->text('mother_phone', 'Mother phone');
        $form->text('emergency_person_name', 'Emergency contact name');
        $form->text('emergency_person_phone', 'Emergency contact phone');

        //SYSTEM ACCOUNT
        $form->divider('System Account');
        $roleModel = config('admin.database.roles_model');
        $roles = $roleModel::where(['slug' => 'parent'])
            ->get()->pluck('name', 'id');

        // NOT required: 410 existing parents have no role row. The parent role is
        // assigned automatically in saving() when this is left empty.
        $form->multipleSelect('roles', trans('admin.roles'))
            ->attribute([
                'autocomplete' => 'off'
            ])
            ->options($roles)
            ->help('Leave empty to assign the Parent role automatically.');

        $ajax_url = url('/api/ajax-users?enterprise_id=' . $u->enterprise_id . "&user_type=student");
        $form->multipleSelect('kids', "Children")
            ->options(function ($ids) {
                if (!is_array($ids)) {
                    return [];
                }
                $data = Administrator::whereIn('id', $ids)->pluck('name', 'id');
                return $data;
            })
            // NOT required: 1,118 existing parents have no child linked yet, and
            // requiring it blocked staff from fixing their contact details.
            ->ajax($ajax_url)
            ->help('Optional here — children can also be linked from the student record.');



        $form->image('avatar', 'Profile photo');

        $form->text('email', 'Email address');

        $form->password('password', trans('admin.password'));

       /*  $form->password('password_confirmation', trans('admin.password_confirmation'))->rules('required')
            ->default(function ($form) {
                return $form->model()->password;
            }); */

        $form->ignore(['password_confirmation']);
        $form->saving(function (Form $form) {
            // ── Mobile number: must be a genuinely valid UG number ───────────
            // Utils::phone_number_is_valid() returns true for everything, which is
            // how "0not set" became the username "+256(not set)" on 241 accounts.
            if (!self::isRealPhone($form->phone_number_1)) {
                $error = new MessageBag([
                    'title'   => 'Invalid mobile phone number',
                    'message' => 'Enter a valid Ugandan mobile number, e.g. 0772123456 or +256772123456.'
                ]);
                return back()->with(compact('error'));
            }
            $prepared_phone_number_1 = Utils::prepare_phone_number($form->phone_number_1);

            // ── Home number: optional, but reject junk if supplied ───────────
            $prepared_phone_number_2 = null;
            if ($form->phone_number_2 !== null && strlen(trim($form->phone_number_2)) > 3) {
                if (!self::isRealPhone($form->phone_number_2)) {
                    $error = new MessageBag([
                        'title'   => 'Invalid home phone number',
                        'message' => 'Enter a valid Ugandan number, or leave the field empty.'
                    ]);
                    return back()->with(compact('error'));
                }
                $prepared_phone_number_2 = Utils::prepare_phone_number($form->phone_number_2);
            }

            $form->phone_number_1 = $prepared_phone_number_1;
            if ($prepared_phone_number_2 !== null) {
                $form->phone_number_2 = $prepared_phone_number_2;
            }

            // ── Username drives phone login, so only set it when it is safe ──
            // Several guardians legitimately share one number (up to 6 here), and
            // there is no unique index — blindly assigning it would create
            // duplicate usernames and make login resolve to an arbitrary account.
            $currentId = $form->model()->id ?? null;
            $usernameTaken = Administrator::where('username', $prepared_phone_number_1)
                ->when($currentId, function ($q) use ($currentId) {
                    return $q->where('id', '!=', $currentId);
                })
                ->exists();

            if (!$usernameTaken) {
                $form->username = $prepared_phone_number_1;
            }
            // else: keep the existing username. Login still works, because
            // findUserByLogin() also matches on phone_number_1.

            // Never fall back to writing a phone number into the email column.
            if ($form->email !== null && trim($form->email) !== '' && !filter_var(trim($form->email), FILTER_VALIDATE_EMAIL)) {
                $form->email = null;
            }

            if ($form->password && $form->model()->password != $form->password) {
                $form->password = Hash::make($form->password);
            }
        });

        $form->saved(function (Form $form) {
            // Guarantee every parent carries the Parent role even when the (now
            // optional) roles field was left empty.
            $model = $form->model();
            if ($model === null || $model->id === null) {
                return;
            }
            if ($model->roles()->count() > 0) {
                return;
            }
            $roleModel = config('admin.database.roles_model');
            $parentRole = $roleModel::where(['slug' => 'parent'])->first();
            if ($parentRole !== null) {
                $model->roles()->attach($parentRole->id);
            }
        });



        return $form;
    }
}
