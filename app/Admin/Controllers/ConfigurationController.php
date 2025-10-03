<?php

namespace App\Admin\Controllers;

use App\Models\Enterprise;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class ConfigurationController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'System Configuration';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Enterprise());
        $grid->disableCreateButton();
        $grid->disableBatchActions();
        $grid->model()->where([
            'id' => Auth::user()->enterprise_id
        ]);

        $grid->column('name', __('School Name'));
        $grid->column('short_name', __('Short name'));
        $grid->column('logo', __('Logo'))->lightbox(['width' => 100, 'height' => 100]);
        $grid->column('phone_number', __('Phone number'));
        $grid->column('email', __('Email'));
        $grid->column('address', __('Address'));


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

        $show->field('name', __('School Name'));
        $show->field('short_name', __('Short name'));
        $show->field('logo', __('Logo'));
        $show->field('phone_number', __('Phone number'));
        $show->field('email', __('Email'));
        $show->field('address', __('Address'));
        $show->field('expiry', __('Expiry'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('subdomain', __('Subdomain'));
        $show->field('color', __('Color'));
        $show->field('welcome_message', __('Welcome message'));
        $show->field('type', __('Type'));

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
        $form->disableCreatingCheck();
        $form->disableReset();
        $form->disableViewCheck();

        $form->text('name', __('School Name'))->required();
        $form->text('motto', __('School Motto'))->required();
        $form->image('logo', __('School badge'))->uniqueName()->required()->required();
        $form->text('address', __('School Address'))->required();
        $form->quill('details', __('School details'));
        $form->text('phone_number', __('Phone number'));
        $form->text('phone_number_2', __('Alternative phone number'));
        $form->text('p_o_box', __('P.O.BOX'));
        $form->text('email', __('Email'));
        $form->color('color', __('School Color'))->default('color')->required();
        $form->color('sec_color', __('Secondary color'))->rules('required')->required();
        $form->quill('welcome_message', __('Welcome message'));
        $form->radio('can_send_messages', __('Enable Message Sending'))
            ->options([
                'Yes' => 'Yes',
                'No' => 'No',
            ])->default('No');


        $form->radio('school_pay_import_automatically', __('Import SchoolPay to Students Accounts Automatically?'))
            ->options([
                'Yes' => 'Yes',
                'No' => 'No',
            ])->default('No')
            ->rules('required');
        $form->date('school_pay_last_accepted_date', __('Last SchoolPay Import Date'))->rules('required')->default(date('Y-m-d'));
        
        $form->divider('Online Student Application Settings');
        
        $form->radio('accepts_online_applications', __('Accept Online Student Applications'))
            ->options([
                'Yes' => 'Yes',
                'No' => 'No',
                'Custom' => 'Custom Message (Applications Closed)',
            ])
            ->default('No')
            ->help('Enable or disable online student application portal');
        
        $form->decimal('application_fee', __('Application Fee'))
            ->default(0.00)
            ->help('Fee amount for student application (0 for free)');
        
        $form->quill('application_instructions', __('Application Instructions'))
            ->help('Instructions that will be shown to applicants on the landing page');
        
        // Required Documents Section
        $form->divider('Required Documents Configuration');
        
        $form->html('<div class="alert alert-info">
            <i class="fa fa-info-circle"></i> 
            <strong>Select Required Documents:</strong> Check the documents you want to require from applicants. 
            You can mark documents as optional or required.
        </div>');
        
        // Common documents with checkboxes
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
            ->help('Student immunization/vaccination records');
        
        $form->checkbox('req_doc_recommendation', __('Recommendation Letter'))
            ->options([
                'required' => 'Required',
                'optional' => 'Optional'
            ])
            ->help('Letter of recommendation from previous school');
        
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
        
        // Custom documents
        $form->html('<div class="alert alert-success" style="margin-top: 20px;">
            <i class="fa fa-plus-circle"></i> 
            <strong>Additional Custom Documents:</strong> Add any other documents specific to your school below (one per line).
        </div>');
        
        $form->textarea('custom_required_documents', __('Custom Documents (One Per Line)'))
            ->rows(5)
            ->placeholder("Enter custom documents, one per line. Example:\nTransfer Certificate\nCharacter Certificate\nFee Clearance")
            ->help('Add any additional documents not listed above. Format: "Document Name|required" or "Document Name|optional"');
        
        // Hidden field to store the compiled JSON (will be populated on save)
        $form->hidden('required_application_documents');
        
        // Save hook to compile checkbox selections into JSON
        $form->saving(function (Form $form) {
            $documents = [];
            
            // Process standard documents
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
            
            foreach ($standardDocs as $field => $name) {
                $value = $form->input($field);
                if (!empty($value)) {
                    $isRequired = in_array('required', $value);
                    $isOptional = in_array('optional', $value);
                    
                    if ($isRequired || $isOptional) {
                        $documents[] = [
                            'name' => $name,
                            'required' => $isRequired
                        ];
                    }
                }
            }
            
            // Process custom documents
            $customDocs = $form->input('custom_required_documents');
            if (!empty($customDocs)) {
                $lines = explode("\n", $customDocs);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;
                    
                    // Check if line has |required or |optional
                    if (strpos($line, '|') !== false) {
                        list($docName, $reqType) = explode('|', $line, 2);
                        $docName = trim($docName);
                        $reqType = trim(strtolower($reqType));
                        
                        if (!empty($docName)) {
                            $documents[] = [
                                'name' => $docName,
                                'required' => ($reqType === 'required')
                            ];
                        }
                    } else {
                        // No requirement specified, default to optional
                        $documents[] = [
                            'name' => $line,
                            'required' => false
                        ];
                    }
                }
            }
            
            // Store as JSON
            $form->required_application_documents = json_encode($documents);
        });
        
        // Load existing JSON data into checkbox fields when editing
        $form->editing(function (Form $form) {
            $jsonData = $form->model()->required_application_documents;
            
            if (!empty($jsonData)) {
                $documents = json_decode($jsonData, true);
                
                if (is_array($documents)) {
                    // Map document names to field names
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
                            $form->model()->{$fieldName} = [$isRequired ? 'required' : 'optional'];
                        } else {
                            // It's a custom document
                            $reqType = $isRequired ? 'required' : 'optional';
                            $customDocs[] = $docName . '|' . $reqType;
                        }
                    }
                    
                    // Set custom documents field
                    if (!empty($customDocs)) {
                        $form->model()->custom_required_documents = implode("\n", $customDocs);
                    }
                }
            }
        });
        
        $form->date('application_deadline', __('Application Deadline'))
            ->help('Last date to accept applications (optional)');
        
        $form->textarea('application_status_message', __('Custom Status Message'))
            ->rows(3)
            ->help('Custom message to show when applications are closed (only used when set to "Custom")');
        
        $form->divider();
        $form->text('hm_name', __('Head Teacher Name'));
        $form->image('hm_signature', __('Head Teacher signature'));
        $form->image('dos_signature', __('D.O.S signature'));
        $form->image('bursar_signature', __('Bursar signature'));

        return $form;
    }
}
