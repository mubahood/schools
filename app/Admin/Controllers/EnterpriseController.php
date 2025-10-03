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

class EnterpriseController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'School Management';

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

        $grid->actions(function ($actions) {
            $actions->disableView();
        });

        $u = Admin::user();
        // Check if user is super admin - if not, filter to their enterprise only
        $userRoles = AdminRoleUser::where('user_id', $u->id)->pluck('role_id')->toArray();
        $superAdminRole = AdminRole::where('slug', 'super-admin')->first();
        if ($superAdminRole && !in_array($superAdminRole->id, $userRoles)) {
            $grid->model()->where('id', '=', $u->enterprise_id);
        }

        $grid->column('id', __('ID'))->sortable()->label('primary');
        $grid->column('logo', __('Logo'))->image('', 40, 40);

        $grid->column('name', __('School Name'))->sortable()->display(function ($name) {
            $shortName = $this->short_name ?? '';
            return "<strong>$name</strong><br><small class='text-muted'>$shortName</small>";
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

        $grid->column('contact_info', __('Contact'))->display(function () {
            $contact = [];
            if ($this->phone_number) {
                $contact[] = "<i class='fa fa-phone'></i> {$this->phone_number}";
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
            return "<span class='$class'>" . date('M d, Y', strtotime($expiry)) . "</span>";
        })->sortable();

        // Filters
        $grid->filter(function ($filter) {
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

        //employees count
        $grid->column('employees_count', __('Employees'))->display(function () {
            return User::where('enterprise_id', $this->id)->where('user_type', 'employee')->count();
        });

        //students count
        $grid->column('students_count', __('Students'))->display(function () {
            return User::where('enterprise_id', $this->id)->where('user_type', 'student')->count();
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
            ->title('School Information')
            ->tools(function ($tools) {
                $tools->disableList();
                $tools->disableDelete();
                $tools->append('<a class="btn btn-sm btn-success" href="' . admin_url('students?enterprise_id=' . $id) . '"><i class="fa fa-users"></i> Students</a>');
                $tools->append('<a class="btn btn-sm btn-info" href="' . admin_url('employees?enterprise_id=' . $id) . '"><i class="fa fa-user-tie"></i> Staff</a>');
                $tools->append('<a class="btn btn-sm btn-warning" href="' . admin_url('academic-years?enterprise_id=' . $id) . '"><i class="fa fa-calendar"></i> Academic Years</a>');
            });

        // Basic Information
        $show->divider('Basic Information');
        $show->field('id', __('School ID'));
        $show->field('logo', __('Logo'))->image();
        $show->field('name', __('School Name'));
        $show->field('short_name', __('Short Name'));
        $show->field('type', __('School Type'))->as(function ($type) {
            $labels = [
                'Primary' => 'label-primary',
                'Secondary' => 'label-info',
                'Advanced' => 'label-success',
                'University' => 'label-warning'
            ];
            $class = $labels[$type] ?? 'label-default';
            return "<span class='label $class'>$type</span>";
        });
        $show->field('motto', __('School Motto'));
        $show->field('welcome_message', __('Welcome Message'))->unescape();

        // Contact Information
        $show->divider('Contact Information');
        $show->field('phone_number', __('Primary Phone'));
        $show->field('phone_number_2', __('Secondary Phone'));
        $show->field('email', __('Email Address'));
        $show->field('website', __('Website'));
        $show->field('address', __('Physical Address'));

        // Administrative Information
        $show->divider('Administrative Information');
        $show->field('administrator_id', __('School Owner'))->as(function ($administrator_id) {
            $owner = User::find($administrator_id);
            return $owner ? $owner->name . ' (' . $owner->email . ')' : 'No Owner Assigned';
        });
        $show->field('hm_name', __('Head Teacher'));

        // Academic Information
        $show->divider('Academic Information');
        $show->field('has_theology', __('Has Theology'))->as(function ($has_theology) {
            return $has_theology == 'Yes' ?
                '<span class="label label-success">Yes</span>' :
                '<span class="label label-default">No</span>';
        });

        // Financial Information
        $show->divider('Financial Information');
        $show->field('wallet_balance', __('Wallet Balance'))->as(function ($balance) {
            return 'UGX ' . number_format($balance ?? 0);
        });

        // School Pay Integration
        $show->divider('SchoolPay Integration');
        $show->field('school_pay_status', __('SchoolPay Status'))->as(function ($status) {
            return $status == 'Yes' ?
                '<span class="label label-success">Enabled</span>' :
                '<span class="label label-danger">Disabled</span>';
        });
        $show->field('school_pay_import_automatically', __('Auto Import'))->as(function ($auto) {
            return $auto == 'Yes' ?
                '<span class="label label-info">Enabled</span>' :
                '<span class="label label-default">Disabled</span>';
        });

        // Online Admissions Configuration
        $show->divider('Online Admissions Settings');
        $show->field('accepts_online_applications', __('Accepts Online Applications'))->as(function ($status) {
            return $status == 'Yes' ?
                '<span class="label label-success"><i class="fa fa-check"></i> Enabled</span>' :
                '<span class="label label-default"><i class="fa fa-times"></i> Disabled</span>';
        });
        $show->field('application_fee', __('Application Fee'))->as(function ($fee) {
            if (!$fee || $fee == 0) return '<span class="text-success">Free Application</span>';
            return 'UGX ' . number_format($fee);
        });
        $show->field('application_deadline', __('Application Deadline'));
        $show->field('application_instructions', __('Application Instructions'))->unescape();
        $show->field('required_application_documents', __('Required Documents'))->as(function ($docs) {
            if (!$docs) return '<span class="text-muted">No documents configured</span>';
            
            $documents = json_decode($docs, true);
            if (!is_array($documents) || empty($documents)) {
                return '<span class="text-muted">No documents configured</span>';
            }
            
            $html = '<ul class="list-unstyled" style="margin: 0;">';
            foreach ($documents as $doc) {
                $name = $doc['name'] ?? 'Unknown';
                $required = $doc['required'] ?? false;
                $badge = $required ? 
                    '<span class="label label-danger">Required</span>' : 
                    '<span class="label label-info">Optional</span>';
                $html .= "<li style='padding: 5px 0;'><i class='fa fa-file-text'></i> {$name} {$badge}</li>";
            }
            $html .= '</ul>';
            return $html;
        });
        
        // License & Expiry
        $show->divider('License Information');
        $show->field('has_valid_lisence', __('License Status'))->as(function ($license) {
            return $license == 'Yes' ?
                '<span class="label label-success"><i class="fa fa-check"></i> Valid License</span>' :
                '<span class="label label-danger"><i class="fa fa-times"></i> Invalid License</span>';
        });
        $show->field('expiry', __('License Expiry'))->as(function ($expiry) {
            if (!$expiry) return '<span class="text-muted">No expiry date set</span>';

            $isExpired = strtotime($expiry) < time();
            $class = $isExpired ? 'text-danger' : 'text-success';
            $status = $isExpired ? ' (EXPIRED)' : '';
            return "<span class='$class'>" . date('M d, Y', strtotime($expiry)) . $status . "</span>";
        });

        // Statistics
        $show->divider('Statistics');
        $show->field('student_count', __('Total Students'))->as(function () {
            return User::where('enterprise_id', $this->id)->where('user_type', 'student')->count();
        });
        $show->field('staff_count', __('Total Staff'))->as(function () {
            return User::where('enterprise_id', $this->id)->where('user_type', 'employee')->count();
        });
        $show->field('academic_years_count', __('Academic Years'))->as(function () {
            return AcademicYear::where('enterprise_id', $this->id)->count();
        });

        // System Information
        $show->divider('System Information');
        $show->field('subdomain', __('Subdomain'));
        $show->field('color', __('Primary Color'))->as(function ($color) {
            return "<span style='display:inline-block;width:20px;height:20px;background:$color;border:1px solid #ccc;'></span> $color";
        });
        $show->field('sec_color', __('Secondary Color'))->as(function ($color) {
            return "<span style='display:inline-block;width:20px;height:20px;background:$color;border:1px solid #ccc;'></span> $color";
        });
        $show->field('created_at', __('Created'));
        $show->field('updated_at', __('Last Updated'));
        $show->field('details', __('Additional Details'));

        return $show;
    }

    /**
     * Make a form builder.
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
                $form->short_name = strtoupper(substr(implode('', array_map(function ($word) {
                    return substr($word, 0, 1);
                }, $words)), 0, 5));
            }
            
            // Compile required documents from checkboxes to JSON
            $documents = [];
            
            // Standard documents mapping
            $standardDocs = [
                'req_doc_birth_certificate' => 'Birth Certificate',
                'req_doc_previous_school_report' => 'Previous School Report',
                'req_doc_passport_photo' => 'Passport Photo',
                'req_doc_parent_id' => 'Parent/Guardian ID',
                'req_doc_immunization' => 'Immunization Records',
                'req_doc_recommendation' => 'Recommendation Letter',
                'req_doc_leaving_certificate' => 'School Leaving Certificate',
                'req_doc_medical_report' => 'Medical Report',
            ];
            
            // Process standard document checkboxes
            foreach ($standardDocs as $field => $name) {
                $value = $form->input($field);
                if (!empty($value) && is_array($value)) {
                    $isRequired = in_array('required', $value);
                    $documents[] = [
                        'name' => $name,
                        'required' => $isRequired
                    ];
                }
            }
            
            // Process custom documents from textarea
            $customDocs = $form->input('custom_required_documents');
            if (!empty($customDocs)) {
                $lines = explode("\n", $customDocs);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;
                    
                    $parts = explode('|', $line);
                    $docName = trim($parts[0]);
                    $docType = isset($parts[1]) ? strtolower(trim($parts[1])) : 'optional';
                    
                    if (!empty($docName)) {
                        $documents[] = [
                            'name' => $docName,
                            'required' => ($docType === 'required')
                        ];
                    }
                }
            }
            
            // Save as JSON
            $form->required_application_documents = json_encode($documents);
        });
        
        // Editing - populate checkboxes from JSON
        $form->editing(function (Form $form) {
            $model = $form->model();
            if ($model && $model->required_application_documents) {
                $documents = json_decode($model->required_application_documents, true);
                
                if (is_array($documents)) {
                    // Standard documents mapping (reverse)
                    $fieldMapping = [
                        'Birth Certificate' => 'req_doc_birth_certificate',
                        'Previous School Report' => 'req_doc_previous_school_report',
                        'Passport Photo' => 'req_doc_passport_photo',
                        'Parent/Guardian ID' => 'req_doc_parent_id',
                        'Immunization Records' => 'req_doc_immunization',
                        'Recommendation Letter' => 'req_doc_recommendation',
                        'School Leaving Certificate' => 'req_doc_leaving_certificate',
                        'Medical Report' => 'req_doc_medical_report',
                    ];
                    
                    $customDocs = [];
                    
                    foreach ($documents as $doc) {
                        $docName = $doc['name'] ?? '';
                        $isRequired = $doc['required'] ?? false;
                        
                        // Check if it's a standard document
                        if (isset($fieldMapping[$docName])) {
                            $fieldName = $fieldMapping[$docName];
                            $value = $isRequired ? ['required'] : ['optional'];
                            $model->setAttribute($fieldName, $value);
                        } else {
                            // It's a custom document
                            $customDocs[] = $docName . '|' . ($isRequired ? 'required' : 'optional');
                        }
                    }
                    
                    // Set custom documents textarea
                    if (!empty($customDocs)) {
                        $model->setAttribute('custom_required_documents', implode("\n", $customDocs));
                    }
                }
            }
        });

        // Basic School Information
        $form->divider('Basic Information');
        
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
            ->default('Primary')
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
            ->help('Does the school offer religious/theology subjects?');

        // Contact & Location Information
        $form->divider('Contact Information');
        
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
            ->rules('nullable|url|max:255')
            ->help('School website URL (e.g., https://school.com)');

        $form->textarea('address', __('Physical Address'))
            ->rows(3)
            ->rules('nullable|string|max:500')
            ->help('Complete physical address of the school');

        // Administrative Information
        $form->divider('Administration');
        
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
            ->rules('nullable')
            ->help('The main administrator who owns this school');

        $form->text('hm_name', __('Head Teacher/Principal Name'))
            ->help('Name of the current head teacher or principal');

        // Visual & Branding
        $form->divider('Branding & Appearance');
        
        $form->color('color', __('Primary Color'))
            ->default('#007bff')
            ->help('Main brand color used throughout the system');

        $form->color('sec_color', __('Secondary Color'))
            ->default('#6c757d')
            ->help('Secondary brand color for accents');

        $form->text('subdomain', __('Subdomain'))
            ->rules('nullable|string|max:50|alpha_dash')
            ->help('Unique subdomain for school (letters, numbers, hyphens only)')
            ->placeholder('e.g., myschool');

        // Financial & Payment Settings
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

        // Online Admissions Settings
        $form->divider('Online Application Portal Settings');
        
        $form->radio('accepts_online_applications', __('Accept Online Applications'))
            ->options([
                'Yes' => 'Yes - Enable online application portal',
                'No' => 'No - Disable online applications',
            ])
            ->default('No')
            ->help('Allow prospective students to apply online through the application portal')
            ->when('Yes', function ($form) {
                
                $form->text('application_deadline', __('Application Deadline'))
                    ->placeholder('e.g., December 31, 2025')
                    ->help('Display text for application deadline (optional)');
                
                $form->currency('application_fee', __('Application Fee'))
                    ->symbol('UGX')
                    ->default(0)
                    ->help('Fee charged for submitting application (0 for free)');
                
                $form->textarea('application_instructions', __('Application Instructions'))
                    ->rows(4)
                    ->placeholder('Provide instructions for applicants...')
                    ->help('Instructions displayed on the application landing page');
                
                $form->quill('custom_application_message', __('Custom Welcome Message'))
                    ->help('Custom message for applicants (optional)');
            });
        
        $form->divider('Required Documents Configuration');
        
        $form->html('<div class="alert alert-info">
            <i class="fa fa-info-circle"></i> <strong>Configure Required Documents</strong><br>
            Select which documents applicants must submit with their application.
        </div>');
        
        // Standard Documents with checkboxes
        $form->checkbox('req_doc_birth_certificate', __('Birth Certificate'))
            ->options([
                'required' => 'Required',
                'optional' => 'Optional'
            ])
            ->help('Birth certificate document');
        
        $form->checkbox('req_doc_previous_school_report', __('Previous School Report'))
            ->options([
                'required' => 'Required',
                'optional' => 'Optional'
            ])
            ->help('Report card from previous school');
        
        $form->checkbox('req_doc_passport_photo', __('Passport Photo'))
            ->options([
                'required' => 'Required',
                'optional' => 'Optional'
            ])
            ->help('Recent passport-sized photograph');
        
        $form->checkbox('req_doc_parent_id', __('Parent/Guardian ID'))
            ->options([
                'required' => 'Required',
                'optional' => 'Optional'
            ])
            ->help('Parent or guardian identification document');
        
        $form->checkbox('req_doc_immunization', __('Immunization Records'))
            ->options([
                'required' => 'Required',
                'optional' => 'Optional'
            ])
            ->help('Medical immunization records');
        
        $form->checkbox('req_doc_recommendation', __('Recommendation Letter'))
            ->options([
                'required' => 'Required',
                'optional' => 'Optional'
            ])
            ->help('Letter of recommendation');
        
        $form->checkbox('req_doc_leaving_certificate', __('School Leaving Certificate'))
            ->options([
                'required' => 'Required',
                'optional' => 'Optional'
            ])
            ->help('Certificate from previous school');
        
        $form->checkbox('req_doc_medical_report', __('Medical Report'))
            ->options([
                'required' => 'Required',
                'optional' => 'Optional'
            ])
            ->help('Recent medical examination report');
        
        $form->divider('Additional Custom Documents');
        
        $form->textarea('custom_required_documents', __('Custom Documents (One Per Line)'))
            ->rows(5)
            ->placeholder("Transfer Certificate|required\nCharacter Certificate|optional\nFee Clearance|required")
            ->help('Add school-specific documents. Format: "Document Name|required" or "Document Name|optional"');
        
        // License & System Settings
        $form->divider('License & System Information');
        
        $form->radio('has_valid_lisence', __('License Status'))
            ->options([
                'Yes' => 'Valid License',
                'No' => 'Invalid/Expired License',
            ])
            ->default('Yes')
            ->help('Current license validity status');

        $form->date('expiry', __('License Expiry Date'))
            ->help('When does the school license expire?');

        $form->divider('Additional Information');
        
        $form->textarea('details', __('Additional Details'))
            ->rows(4)
            ->help('Any additional information about the school');

        // Custom CSS and JavaScript for enhanced UX
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
                
                // Validate colors
                $("input[type=color]").on("change", function() {
                    var color = $(this).val();
                    $(this).closest(".form-group").find(".help-block").html("Selected: " + color);
                });
            });
        </script>');

        return $form;
    }
}
