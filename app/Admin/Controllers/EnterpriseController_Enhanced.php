<?php

namespace App\Admin\Controllers;

use App\Models\Enterprise;
use App\Models\User;
use App\Models\AcademicYear;
use App\Models\AdminRole;
use App\Models\AdminRoleUser;
use App\Models\Term;
use App\Models\Account;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;

class EnterpriseControllerEnhanced extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'School Management - Enhanced';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Enterprise());
        $grid->disableBatchActions();
        $grid->quickSearch('name', 'id', 'short_name', 'email', 'phone_number')->placeholder('Search by name, ID, email or phone');

        $grid->model()->orderBy('id', 'DESC');

        // Access control
        $u = Admin::user();
        $userRoles = AdminRoleUser::where('user_id', $u->id)->pluck('role_id')->toArray();
        $superAdminRole = AdminRole::where('slug', 'super-admin')->first();
        if ($superAdminRole && !in_array($superAdminRole->id, $userRoles)) {
            $grid->model()->where('id', '=', $u->enterprise_id);
        }

        $grid->actions(function ($actions) {
            $actions->disableView();
        });

        // Grid columns
        $grid->column('id', __('ID'))->sortable()->label('primary');
        $grid->column('logo', __('Logo'))->image('', 40, 40);
        
        $grid->column('name', __('School Name'))->sortable()->display(function ($name) {
            $shortName = $this->short_name ?? '';
            return "<strong>$name</strong>" . ($shortName ? "<br><small class='text-muted'>$shortName</small>" : "");
        });
        
        $grid->column('type', __('Type'))->label([
            'Primary' => 'primary',
            'Secondary' => 'info',
            'Advanced' => 'success',
            'University' => 'warning'
        ])->sortable();
        
        $grid->column('administrator_id', __('Owner'))->display(function ($value) {
            $owner = User::find($value);
            if (!$owner) {
                return '<span class="text-muted">No Owner</span>';
            }
            return "<strong>{$owner->name}</strong><br><small class='text-muted'>{$owner->email}</small>";
        });
        
        $grid->column('phone_number', __('Contact'))->display(function ($phone) {
            $contact = [];
            if ($phone) {
                $contact[] = "<i class='fa fa-phone'></i> $phone";
            }
            if ($this->email) {
                $contact[] = "<i class='fa fa-envelope'></i> {$this->email}";
            }
            return implode('<br>', $contact) ?: '<span class="text-muted">No contact</span>';
        });
        
        $grid->column('has_valid_lisence', __('License'))->display(function ($value) {
            if ($value == 'Yes') {
                return "<span class='label label-success'><i class='fa fa-check'></i> Valid</span>";
            } else {
                return "<span class='label label-danger'><i class='fa fa-times'></i> Invalid</span>";
            }
        });

        $grid->column('school_pay_status', __('SchoolPay'))->display(function ($value) {
            if ($value == 'Yes') {
                return "<span class='label label-info'><i class='fa fa-credit-card'></i> Enabled</span>";
            } else {
                return "<span class='label label-default'><i class='fa fa-ban'></i> Disabled</span>";
            }
        });
        
        $grid->column('wallet_balance', __('Wallet'))->display(function ($value) {
            return 'UGX ' . number_format($value ?? 0);
        })->sortable();
        
        $grid->column('created_at', __('Created'))->display(function ($created_at) {
            return date('M d, Y', strtotime($created_at));
        })->sortable();
        
        $grid->column('expiry', __('Expires'))->display(function ($expiry) {
            if (!$expiry) return '<span class="text-muted">No expiry</span>';
            
            $isExpired = strtotime($expiry) < time();
            $class = $isExpired ? 'text-danger' : 'text-success';
            $status = $isExpired ? ' (EXPIRED)' : '';
            return "<span class='$class'>" . date('M d, Y', strtotime($expiry)) . "$status</span>";
        })->sortable();

        // Enhanced filtering
        $grid->filter(function($filter) {
            $filter->disableIdFilter();
            
            $filter->like('name', 'School Name');
            $filter->equal('type', 'School Type')->select([
                'Primary' => 'Primary',
                'Secondary' => 'Secondary', 
                'Advanced' => 'Advanced',
                'University' => 'University'
            ]);
            $filter->equal('has_valid_lisence', 'License Status')->select([
                'Yes' => 'Licensed',
                'No' => 'Unlicensed'
            ]);
            $filter->equal('school_pay_status', 'SchoolPay Status')->select([
                'Yes' => 'Enabled',
                'No' => 'Disabled'
            ]);
            $filter->between('created_at', 'Created Date')->date();
        });

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
        $show = new Show(Enterprise::findOrFail($id));

        $show->panel()
            ->style('info')
            ->title('School Management Dashboard')
            ->tools(function ($tools) use ($id) {
                $tools->disableList();
                $tools->disableDelete();
                $tools->append('<a class="btn btn-sm btn-success" href="'.admin_url('students?enterprise_id='.$id).'"><i class="fa fa-users"></i> Students</a>');
                $tools->append('<a class="btn btn-sm btn-info" href="'.admin_url('employees?enterprise_id='.$id).'"><i class="fa fa-user-tie"></i> Staff</a>');
                $tools->append('<a class="btn btn-sm btn-warning" href="'.admin_url('academic-years?enterprise_id='.$id).'"><i class="fa fa-calendar"></i> Academic Years</a>');
            });

        // Basic Information
        $show->divider('Basic Information');
        $show->field('id', __('School ID'));
        $show->field('name', __('School Name'));
        $show->field('short_name', __('Short Name'));
        $show->field('type', __('School Type'));
        $show->field('motto', __('School Motto'));
        $show->field('logo', __('Logo'))->image();
        
        // Contact Information
        $show->divider('Contact Information');
        $show->field('phone_number', __('Primary Phone'));
        $show->field('phone_number_2', __('Secondary Phone'));
        $show->field('email', __('Email Address'));
        $show->field('website', __('Website'))->link();
        $show->field('address', __('Physical Address'));
        
        // Administrative Information
        $show->divider('Administrative Details');
        $show->field('administrator_id', __('School Owner'))->as(function ($value) {
            $owner = User::find($value);
            return $owner ? "{$owner->name} ({$owner->email})" : 'No owner assigned';
        });
        $show->field('hm_name', __('Head Teacher/Principal'));
        
        // Academic & System Information
        $show->divider('Academic Information');
        $show->field('has_theology', __('Religious Studies'));
        $show->field('welcome_message', __('Welcome Message'))->unescape();
        
        // Statistics
        $show->divider('Statistics');
        $show->field('students_count', __('Total Students'))->as(function () {
            return User::where('enterprise_id', $this->id)->where('user_type', 'student')->count();
        });
        $show->field('staff_count', __('Total Staff'))->as(function () {
            return User::where('enterprise_id', $this->id)->where('user_type', 'employee')->count();
        });
        $show->field('academic_years_count', __('Academic Years'))->as(function () {
            return AcademicYear::where('enterprise_id', $this->id)->count();
        });
        
        // Financial Information
        $show->divider('Financial & Payment Settings');
        $show->field('school_pay_status', __('SchoolPay Status'));
        $show->field('school_pay_code', __('SchoolPay Code'));
        $show->field('wallet_balance', __('Wallet Balance'))->as(function ($value) {
            return 'UGX ' . number_format($value ?? 0);
        });
        
        // License & System
        $show->divider('License & System Information');
        $show->field('has_valid_lisence', __('License Status'));
        $show->field('expiry', __('License Expiry Date'));
        $show->field('subdomain', __('Subdomain'));
        $show->field('color', __('Primary Color'))->color();
        $show->field('sec_color', __('Secondary Color'))->color();
        
        // System Information
        $show->divider('System Information');
        $show->field('created_at', __('Created At'));
        $show->field('updated_at', __('Last Updated'));
        $show->field('details', __('Additional Details'));

        return $show;
    }

    /**
     * Make a form builder - Enhanced version with tabbed interface
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Enterprise());

        $u = Admin::user();

        // Custom validation and saving logic
        $form->saving(function (Form $form) {
            // Ensure unique subdomain if provided
            if ($form->subdomain) {
                $form->subdomain = strtolower(trim($form->subdomain));
                $existing = Enterprise::where('subdomain', $form->subdomain)
                    ->where('id', '!=', $form->model()->id ?? 0)
                    ->first();
                if ($existing) {
                    $error = new \Illuminate\Support\MessageBag(['subdomain' => ['Subdomain already exists.']]);
                    return back()->with(compact('error'));
                }
            }

            // Auto-generate short name if not provided
            if (!$form->short_name && $form->name) {
                $words = explode(' ', $form->name);
                $form->short_name = strtoupper(substr(implode('', array_map(function($word) {
                    return substr($word, 0, 1);
                }, $words)), 0, 5));
            }
        });

        // Basic School Information
        $form->tab('Basic Information', function ($form) use ($u) {
            $form->text('name', __('School Name'))
                ->rules('required|string|max:255')
                ->help('Full official name of the school');
            
            $form->text('short_name', __('Short Name/Abbreviation'))
                ->rules('string|max:20')
                ->help('e.g., SMS for St. Mary\'s School');
            
            $form->radio('type', __('School Type'))
                ->options([
                    'Primary' => 'Primary School',
                    'Secondary' => 'Secondary School (O\'Level)',
                    'Advanced' => 'Advanced School (O\'Level + A\'Level)',
                    'University' => 'University/Tertiary Institution',
                ])
                ->rules('required')
                ->help('Select the type of educational institution');
            
            $form->text('motto', __('School Motto'))
                ->help('School motto or slogan');
            
            $form->quill('welcome_message', __('Welcome Message'))
                ->help('Message displayed on school dashboard and reports');
            
            $form->image('logo', __('School Logo'))
                ->help('Upload school logo (recommended: 200x200px, PNG/JPG)');
            
            $form->radio('has_theology', __('Has Religious Studies'))
                ->options([
                    'Yes' => 'Yes',
                    'No' => 'No',
                ])
                ->default('No')
                ->rules('required')
                ->help('Does the school offer religious/theology subjects?');
        });

        // Contact & Location Information
        $form->tab('Contact Information', function ($form) {
            $form->text('phone_number', __('Primary Phone Number'))
                ->rules('required|string|max:20')
                ->help('Main contact number for the school');
            
            $form->text('phone_number_2', __('Secondary Phone Number'))
                ->rules('string|max:20')
                ->help('Alternative contact number');
            
            $form->email('email', __('Email Address'))
                ->rules('required|email|max:255')
                ->help('Official school email address');
            
            $form->url('website', __('Website URL'))
                ->rules('url|max:255')
                ->help('School website URL (e.g., https://school.com)');
            
            $form->textarea('address', __('Physical Address'))
                ->rows(3)
                ->rules('required|string|max:500')
                ->help('Complete physical address of the school');
        });

        // Administrative Information
        $form->tab('Administration', function ($form) use ($u) {
            $ajax_url = url(
                '/api/ajax?'
                    . 'enterprise_id=' . $u->enterprise_id
                    . "&search_by_1=name"
                    . "&search_by_2=email"
                    . "&model=User"
            );

            $form->select('administrator_id', __('School Owner/Administrator'))
                ->ajax($ajax_url)
                ->options(function ($id) {
                    $admin = User::find($id);
                    if ($admin) {
                        return [$admin->id => "#{$admin->id} - {$admin->name} ({$admin->email})"];
                    }
                })
                ->rules('required')
                ->help('The main administrator who owns this school');
            
            $form->text('hm_name', __('Head Teacher/Principal Name'))
                ->help('Name of the current head teacher or principal');
        });

        // Visual & Branding
        $form->tab('Branding & Appearance', function ($form) {
            $form->color('color', __('Primary Color'))
                ->default('#007bff')
                ->rules('required')
                ->help('Main brand color used throughout the system');
            
            $form->color('sec_color', __('Secondary Color'))
                ->default('#6c757d')
                ->rules('required')
                ->help('Secondary brand color for accents');
            
            $form->text('subdomain', __('Subdomain'))
                ->rules('string|max:50|alpha_dash')
                ->help('Unique subdomain for school (letters, numbers, hyphens only)')
                ->placeholder('e.g., myschool');
        });

        // Financial & Payment Settings
        $form->tab('Financial Settings', function ($form) {
            $form->divider('SchoolPay Integration');
            
            $form->radio('school_pay_status', __('SchoolPay Status'))
                ->options([
                    'Yes' => 'Enabled',
                    'No' => 'Disabled',
                ])
                ->default('No')
                ->when('Yes', function ($form) {
                    $form->text('school_pay_code', __('SchoolPay Institution Code'))
                        ->rules('required_if:school_pay_status,Yes')
                        ->help('Your SchoolPay institution code');
                    
                    $form->password('school_pay_password', __('SchoolPay API Password'))
                        ->rules('required_if:school_pay_status,Yes')
                        ->help('SchoolPay API access password');
                    
                    $form->radio('school_pay_import_automatically', __('Auto Import Transactions'))
                        ->options([
                            'Yes' => 'Yes - Import automatically',
                            'No' => 'No - Manual import only',
                        ])
                        ->default('No')
                        ->help('Automatically import SchoolPay transactions?');
                    
                    $form->date('school_pay_last_accepted_date', __('Import From Date'))
                        ->default(date('Y-01-01'))
                        ->help('Import transactions from this date onwards');
                })
                ->help('Enable SchoolPay payment gateway integration');
            
            $form->divider('Wallet Settings');
            $form->currency('wallet_balance', __('Current Wallet Balance'))
                ->symbol('UGX')
                ->readonly()
                ->help('Current school wallet balance (managed automatically)');
        });

        // License & System Settings
        $form->tab('License & System', function ($form) {
            $form->radio('has_valid_lisence', __('License Status'))
                ->options([
                    'Yes' => 'Valid License',
                    'No' => 'Invalid/Expired License',
                ])
                ->default('Yes')
                ->rules('required')
                ->help('Current license validity status');
            
            $form->date('expiry', __('License Expiry Date'))
                ->help('When does the school license expire?');
            
            $form->divider('Additional Information');
            $form->textarea('details', __('Additional Details'))
                ->rows(4)
                ->help('Any additional information about the school');
        });

        // JavaScript for enhanced UX
        $form->html('<script>
            $(document).ready(function() {
                // Auto-generate short name from school name
                $("input[name=name]").on("input", function() {
                    if (!$("input[name=short_name]").val()) {
                        var name = $(this).val();
                        var words = name.split(" ");
                        var shortName = "";
                        words.forEach(function(word) {
                            if (word.length > 0) {
                                shortName += word.charAt(0).toUpperCase();
                            }
                        });
                        $("input[name=short_name]").val(shortName.substring(0, 5));
                    }
                });
                
                // Auto-generate subdomain from school name
                $("input[name=name]").on("input", function() {
                    if (!$("input[name=subdomain]").val()) {
                        var name = $(this).val().toLowerCase();
                        var subdomain = name.replace(/[^a-z0-9]/g, "").substring(0, 20);
                        $("input[name=subdomain]").val(subdomain);
                    }
                });
            });
        </script>');

        return $form;
    }
}
