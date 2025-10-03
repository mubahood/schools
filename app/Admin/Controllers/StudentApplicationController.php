<?php

namespace App\Admin\Controllers;

use App\Models\StudentApplication;
use App\Models\Enterprise;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StudentApplicationController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Student Applications';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new StudentApplication());
        
        // Filter by enterprise
        $grid->model()->where('selected_enterprise_id', Admin::user()->enterprise_id);
        
        // Order by most recent
        $grid->model()->orderBy('created_at', 'desc');
        
        // Disable batch actions for security
        $grid->disableBatchActions();
        
        // Disable create button (applications come from public portal)
        $grid->disableCreateButton();
        
        // Filters
        $grid->filter(function($filter) {
            $filter->disableIdFilter();
            
            $filter->like('application_number', 'Application Number');
            $filter->like('first_name', 'First Name');
            $filter->like('last_name', 'Last Name');
            $filter->like('email', 'Email');
            $filter->like('phone_number', 'Phone Number');
            
            $filter->equal('status', 'Status')->select([
                'draft' => 'Draft',
                'submitted' => 'Submitted',
                'under_review' => 'Under Review',
                'accepted' => 'Accepted',
                'rejected' => 'Rejected',
                'cancelled' => 'Cancelled'
            ]);
            
            $filter->equal('gender', 'Gender')->select([
                'male' => 'Male',
                'female' => 'Female'
            ]);
            
            $filter->like('applying_for_class', 'Applying For Class');
            
            $filter->between('created_at', 'Application Date')->datetime();
            $filter->between('submitted_at', 'Submission Date')->datetime();
        });
        
        // Quick Search
        $grid->quickSearch('application_number', 'first_name', 'last_name', 'email', 'phone_number');
        
        // Columns
        $grid->column('application_number', __('Application #'))
             ->display(function($number) {
                 return "<strong style='color: #0d6efd;'>$number</strong>";
             })
             ->sortable();
        
        $grid->column('full_name', __('Applicant'))
             ->display(function() {
                 $name = $this->first_name . ' ' . $this->last_name;
                 $email = $this->email ?? '';
                 $phone = $this->phone_number ?? '';
                 return "<div style='line-height: 1.4;'>
                     <strong>$name</strong><br/>
                     <small class='text-muted'><i class='fa fa-envelope'></i> $email</small><br/>
                     <small class='text-muted'><i class='fa fa-phone'></i> $phone</small>
                 </div>";
             });
        
        $grid->column('date_of_birth', __('Age'))
             ->display(function($dob) {
                 if (!$dob) return '-';
                 $age = Carbon::parse($dob)->age;
                 return "$age years";
             });
        
        $grid->column('gender', __('Gender'))
             ->display(function($gender) {
                 if (!$gender) return '-';
                 $icon = strtolower($gender) === 'male' ? 'male' : 'female';
                 $color = strtolower($gender) === 'male' ? '#0d6efd' : '#d63384';
                 return "<i class='fa fa-$icon' style='color: $color;'></i> " . ucfirst($gender);
             });
        
        $grid->column('applying_for_class', __('Class'))
             ->label('info')
             ->sortable();
        
        $grid->column('status', __('Status'))
             ->display(function($status) {
                 $badges = [
                     'draft' => ['secondary', 'Draft'],
                     'submitted' => ['primary', 'Submitted'],
                     'under_review' => ['info', 'Under Review'],
                     'accepted' => ['success', 'Accepted'],
                     'rejected' => ['danger', 'Rejected'],
                     'cancelled' => ['warning', 'Cancelled']
                 ];
                 
                 $badge = $badges[$status] ?? ['secondary', ucwords(str_replace('_', ' ', $status))];
                 return "<span class='badge badge-{$badge[0]}'><i class='fa fa-circle'></i> {$badge[1]}</span>";
             })
             ->sortable();
        
        $grid->column('progress_percentage', __('Progress'))
             ->display(function($progress) {
                 $color = $progress < 30 ? 'danger' : ($progress < 70 ? 'warning' : 'success');
                 return "<div class='progress' style='height: 20px;'>
                     <div class='progress-bar bg-$color' role='progressbar' 
                          style='width: {$progress}%' 
                          aria-valuenow='$progress' aria-valuemin='0' aria-valuemax='100'>
                         {$progress}%
                     </div>
                 </div>";
             });
        
        $grid->column('attachments', __('Attachments'))
             ->display(function($attachments) {
                 if (empty($attachments) || !is_array($attachments)) {
                     return "<small class='text-muted'>None</small>";
                 }
                 $count = count($attachments);
                 return "<span class='badge badge-info'><i class='fa fa-paperclip'></i> $count file(s)</span>";
             });
        
        $grid->column('submitted_at', __('Submitted'))
             ->display(function($date) {
                 if (!$date) return '<small class="text-muted">Not yet</small>';
                 return "<small>" . Carbon::parse($date)->format('M d, Y H:i') . "</small>";
             })
             ->sortable();
        
        $grid->column('created_at', __('Applied On'))
             ->display(function($date) {
                 return Carbon::parse($date)->format('M d, Y');
             })
             ->sortable();
        
        // Actions
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            // Enable edit button
            $actions->disableEdit(false);
            
            // Add review button for submitted/under_review applications
            if (in_array($actions->row->status, ['submitted', 'under_review'])) {
                $reviewUrl = admin_url("student-applications/{$actions->getKey()}/review");
                $actions->append("<a href='$reviewUrl' class='btn btn-sm btn-primary' title='Review Application'>
                    <i class='fa fa-check-circle'></i> Review
                </a>");
            }
            
            // Add accept/reject quick actions
            if ($actions->row->status === 'submitted' || $actions->row->status === 'under_review') {
                $actions->append("
                    <a href='javascript:void(0);' 
                       class='btn btn-sm btn-success accept-application' 
                       data-id='{$actions->getKey()}'
                       title='Accept Application'>
                        <i class='fa fa-check'></i>
                    </a>
                    <a href='javascript:void(0);' 
                       class='btn btn-sm btn-danger reject-application' 
                       data-id='{$actions->getKey()}'
                       title='Reject Application'>
                        <i class='fa fa-times'></i>
                    </a>
                ");
            }
        });
        
        // Add custom scripts for quick actions
        Admin::script($this->getGridScript());
        
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Content
     */
    public function show($id, Content $content)
    {
        $application = StudentApplication::findOrFail($id);
        
        // Verify it belongs to this enterprise
        if ($application->selected_enterprise_id != Admin::user()->enterprise_id) {
            return redirect()
                ->back()
                ->with('error', 'You do not have permission to view this application.');
        }
        
        return $content
            ->title('Application Details')
            ->description($application->application_number)
            ->body(view('admin.student-application-detail', [
                'application' => $application
            ]));
    }
    
    /**
     * Make a show builder (legacy support).
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(StudentApplication::findOrFail($id));
        
        // Application Information
        $show->panel()
             ->title('Application Information')
             ->style('primary');
        
        $show->field('application_number', __('Application Number'));
        $show->field('status', __('Status'))->as(function($status) {
            return ucwords(str_replace('_', ' ', $status));
        })->badge();
        $show->field('progress_percentage', __('Progress'))->progressBar();
        
        // Personal Information
        $show->panel()
             ->title('Personal Information')
             ->style('info');
        
        $show->field('first_name', __('First Name'));
        $show->field('middle_name', __('Middle Name'));
        $show->field('last_name', __('Last Name'));
        $show->field('date_of_birth', __('Date of Birth'));
        $show->field('gender', __('Gender'));
        $show->field('nationality', __('Nationality'));
        $show->field('religion', __('Religion'));
        
        // Contact Information
        $show->panel()
             ->title('Contact Information')
             ->style('success');
        
        $show->field('email', __('Email'));
        $show->field('phone_number', __('Phone Number'));
        $show->field('phone_number_2', __('Alternative Phone'));
        $show->field('home_address', __('Home Address'));
        $show->field('district', __('District'));
        $show->field('city', __('City'));
        $show->field('village', __('Village'));
        
        // Parent/Guardian Information
        $show->panel()
             ->title('Parent/Guardian Information')
             ->style('warning');
        
        $show->field('parent_name', __('Parent Name'));
        $show->field('parent_relationship', __('Relationship'));
        $show->field('parent_phone', __('Parent Phone'));
        $show->field('parent_email', __('Parent Email'));
        $show->field('parent_address', __('Parent Address'));
        
        // Previous School Information
        $show->panel()
             ->title('Previous School Information')
             ->style('secondary');
        
        $show->field('previous_school', __('Previous School'));
        $show->field('previous_class', __('Previous Class'));
        $show->field('year_completed', __('Year Completed'));
        
        // Application Details
        $show->panel()
             ->title('Application Details')
             ->style('primary');
        
        $show->field('applying_for_class', __('Applying For Class'));
        $show->field('selectedEnterprise.name', __('Selected School'));
        $show->field('special_needs', __('Special Needs/Requirements'));
        
        $show->divider();
        
        // Attachments Section
        $show->field('attachments', __('Supporting Documents'))
             ->as(function($attachments) {
                 if (empty($attachments) || !is_array($attachments)) {
                     return '<p class="text-muted">No documents attached</p>';
                 }
                 
                 $html = '<div class="table-responsive">
                     <table class="table table-bordered table-hover">
                         <thead class="bg-light">
                             <tr>
                                 <th width="40">#</th>
                                 <th>Document Name</th>
                                 <th width="100">Size</th>
                                 <th width="150">Uploaded Date</th>
                                 <th width="100">Actions</th>
                             </tr>
                         </thead>
                         <tbody>';
                 
                 foreach ($attachments as $index => $doc) {
                     $num = $index + 1;
                     $name = htmlspecialchars($doc['name'] ?? 'Unknown');
                     $size = number_format(($doc['size'] ?? 0) / 1024, 2) . ' KB';
                     $date = isset($doc['uploaded_at']) ? Carbon::parse($doc['uploaded_at'])->format('M d, Y H:i') : '-';
                     $path = $doc['path'] ?? '';
                     $url = $path ? asset('storage/' . $path) : '#';
                     
                     // Get file icon based on type
                     $type = $doc['type'] ?? '';
                     $icon = 'file';
                     $iconColor = '#6c757d';
                     
                     if (str_contains($type, 'pdf')) {
                         $icon = 'file-pdf-o';
                         $iconColor = '#dc3545';
                     } elseif (str_contains($type, 'image')) {
                         $icon = 'file-image-o';
                         $iconColor = '#198754';
                     } elseif (str_contains($type, 'word') || str_contains($type, 'doc')) {
                         $icon = 'file-word-o';
                         $iconColor = '#0d6efd';
                     }
                     
                     $html .= "
                         <tr>
                             <td class='text-center'>$num</td>
                             <td>
                                 <i class='fa fa-$icon' style='color: $iconColor;'></i> 
                                 $name
                             </td>
                             <td>$size</td>
                             <td>$date</td>
                             <td>
                                 <a href='$url' target='_blank' class='btn btn-sm btn-primary'>
                                     <i class='fa fa-download'></i> View
                                 </a>
                             </td>
                         </tr>
                     ";
                 }
                 
                 $html .= '</tbody></table></div>';
                 return $html;
             });
        
        $show->divider();
        
        // Timestamps
        $show->panel()
             ->title('Application Timeline')
             ->style('default');
        
        $show->field('started_at', __('Started At'))
             ->as(function($date) {
                 return $date ? Carbon::parse($date)->format('F d, Y H:i:s') : '-';
             });
        
        $show->field('submitted_at', __('Submitted At'))
             ->as(function($date) {
                 return $date ? Carbon::parse($date)->format('F d, Y H:i:s') : '-';
             });
        
        $show->field('reviewed_at', __('Reviewed At'))
             ->as(function($date) {
                 return $date ? Carbon::parse($date)->format('F d, Y H:i:s') : '-';
             });
        
        $show->field('completed_at', __('Completed At'))
             ->as(function($date) {
                 return $date ? Carbon::parse($date)->format('F d, Y H:i:s') : '-';
             });
        
        $show->field('created_at', __('Record Created'))
             ->as(function($date) {
                 return Carbon::parse($date)->format('F d, Y H:i:s');
             });
        
        $show->field('updated_at', __('Last Updated'))
             ->as(function($date) {
                 return Carbon::parse($date)->format('F d, Y H:i:s');
             });
        
        // Admin Review Section (if reviewed)
        $application = StudentApplication::find($id);
        if ($application && $application->reviewed_by) {
            $show->divider();
            
            $show->panel()
                 ->title('Administrative Review')
                 ->style('info');
            
            $show->field('reviewer.name', __('Reviewed By'));
            $show->field('admin_notes', __('Admin Notes'));
            $show->field('rejection_reason', __('Rejection Reason'));
        }
        
        return $show;
    }

    /**
     * Review an application (custom page)
     */
    public function review($id, Content $content)
    {
        $application = StudentApplication::findOrFail($id);
        
        // Verify it belongs to this enterprise
        if ($application->selected_enterprise_id != Admin::user()->enterprise_id) {
            return redirect()
                ->back()
                ->with('error', 'You do not have permission to review this application.');
        }
        
        return $content
            ->title('Review Application')
            ->description($application->application_number)
            ->body(view('admin.student-application-review', [
                'application' => $application
            ]));
    }

    /**
     * Accept an application
     */
    public function accept($id, Request $request)
    {
        $application = StudentApplication::findOrFail($id);
        
        // Verify it belongs to this enterprise
        if ($application->selected_enterprise_id != Admin::user()->enterprise_id) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to accept this application.'
            ], 403);
        }
        
        // Verify it can be accepted
        if (!$application->canReview()) {
            return response()->json([
                'success' => false,
                'message' => 'This application cannot be accepted in its current status.'
            ], 422);
        }
        
        try {
            $notes = $request->input('notes');
            $application->accept(Admin::user()->id, $notes);
            
            return response()->json([
                'success' => true,
                'message' => 'Application accepted successfully! Student account has been created.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to accept application: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject an application
     */
    public function reject($id, Request $request)
    {
        $application = StudentApplication::findOrFail($id);
        
        // Verify it belongs to this enterprise
        if ($application->selected_enterprise_id != Admin::user()->enterprise_id) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to reject this application.'
            ], 403);
        }
        
        // Verify it can be rejected
        if (!$application->canReview()) {
            return response()->json([
                'success' => false,
                'message' => 'This application cannot be rejected in its current status.'
            ], 422);
        }
        
        // Validate reason
        if (empty($request->input('reason'))) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide a reason for rejection.'
            ], 422);
        }
        
        try {
            $reason = $request->input('reason');
            $notes = $request->input('notes');
            
            $application->reject($reason, Admin::user()->id, $notes);
            
            return response()->json([
                'success' => true,
                'message' => 'Application rejected successfully.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject application: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * View/Download an attachment
     */
    public function viewAttachment($id, $attachmentIndex)
    {
        $application = StudentApplication::findOrFail($id);
        
        // Verify permissions
        if ($application->selected_enterprise_id != Admin::user()->enterprise_id) {
            abort(403, 'You do not have permission to view this attachment.');
        }
        
        $attachments = $application->attachments ?? [];
        
        if (!isset($attachments[$attachmentIndex])) {
            abort(404, 'Attachment not found.');
        }
        
        $attachment = $attachments[$attachmentIndex];
        $filePath = $attachment['path'] ?? '';
        
        if (!$filePath || !Storage::disk('public')->exists($filePath)) {
            abort(404, 'File not found on storage.');
        }
        
        return response()->download(
            storage_path('app/public/' . $filePath),
            $attachment['name'] ?? 'document'
        );
    }

    /**
     * Get grid JavaScript for quick actions
     */
    protected function getGridScript()
    {
        $acceptUrl = url(config('admin.route.prefix') . '/student-applications');
        $rejectUrl = url(config('admin.route.prefix') . '/student-applications');
        
        return <<<SCRIPT
        $(document).on('click', '.accept-application', function() {
            var id = $(this).data('id');
            
            swal({
                title: "Accept Application",
                text: "Enter acceptance notes (optional):",
                content: {
                    element: "input",
                    attributes: {
                        placeholder: "Notes about acceptance...",
                        type: "text",
                    },
                },
                buttons: {
                    cancel: "Cancel",
                    confirm: {
                        text: "Accept Application",
                        closeModal: false,
                    }
                },
            }).then(function(notes) {
                if (notes === null) return;
                
                $.ajax({
                    url: '{$acceptUrl}/' + id + '/accept',
                    type: 'POST',
                    data: {
                        notes: notes,
                        _token: LA.token
                    },
                    success: function(response) {
                        swal({
                            title: "Success!",
                            text: response.message,
                            icon: "success",
                        }).then(function() {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        var message = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred';
                        swal("Error!", message, "error");
                    }
                });
            });
        });
        
        $(document).on('click', '.reject-application', function() {
            var id = $(this).data('id');
            
            swal({
                title: "Reject Application",
                text: "Enter rejection reason (required):",
                content: {
                    element: "input",
                    attributes: {
                        placeholder: "Reason for rejection...",
                        type: "text",
                    },
                },
                buttons: {
                    cancel: "Cancel",
                    confirm: {
                        text: "Reject Application",
                        closeModal: false,
                    }
                },
            }).then(function(reason) {
                if (reason === null) return;
                
                if (!reason || reason.trim().length < 10) {
                    swal("Error!", "Please provide a detailed reason (at least 10 characters)", "error");
                    return;
                }
                
                $.ajax({
                    url: '{$rejectUrl}/' + id + '/reject',
                    type: 'POST',
                    data: {
                        reason: reason,
                        _token: LA.token
                    },
                    success: function(response) {
                        swal({
                            title: "Rejected",
                            text: response.message,
                            icon: "success",
                        }).then(function() {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        var message = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred';
                        swal("Error!", message, "error");
                    }
                });
            });
        });
SCRIPT;
    }

    /**
     * Make a form builder.
     * Note: Allows editing of application details and status
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new StudentApplication());
        
        // Disable form operations
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });
        
        // Filter by enterprise
        if ($form->isCreating()) {
            $form->hidden('selected_enterprise_id')->default(Admin::user()->enterprise_id);
        }
        
        // Application Information
        $form->divider('Application Information');
        
        $form->display('application_number', __('Application Number'));
        
        $form->select('status', __('Application Status'))
             ->options([
                 'draft' => 'Draft',
                 'submitted' => 'Submitted',
                 'under_review' => 'Under Review',
                 'accepted' => 'Accepted',
                 'rejected' => 'Rejected',
                 'cancelled' => 'Cancelled'
             ])
             ->required()
             ->rules('required')
             ->help('Change application status');
        
        // Personal Information
        $form->divider('Personal Information');
        
        $form->text('first_name', __('First Name'))
             ->required()
             ->rules('required|string|max:100');
        
        $form->text('middle_name', __('Middle Name'))
             ->rules('nullable|string|max:100');
        
        $form->text('last_name', __('Last Name'))
             ->required()
             ->rules('required|string|max:100');
        
        $form->date('date_of_birth', __('Date of Birth'))
             ->format('YYYY-MM-DD')
             ->rules('required|date');
        
        $form->select('gender', __('Gender'))
             ->options([
                 'male' => 'Male',
                 'female' => 'Female'
             ])
             ->required()
             ->rules('required|in:male,female');
        
        $form->text('nationality', __('Nationality'))
             ->rules('nullable|string|max:100');
        
        $form->text('religion', __('Religion'))
             ->rules('nullable|string|max:100');
        
        // Contact Information
        $form->divider('Contact Information');
        
        $form->email('email', __('Email Address'))
             ->rules('required|email|max:255');
        
        $form->text('phone_number', __('Phone Number'))
             ->rules('required|string|max:20');
        
        $form->text('phone_number_2', __('Alternative Phone'))
             ->rules('nullable|string|max:20');
        
        $form->textarea('home_address', __('Home Address'))
             ->rows(3)
             ->rules('nullable|string|max:500');
        
        $form->text('district', __('District'))
             ->rules('nullable|string|max:100');
        
        $form->text('city', __('City'))
             ->rules('nullable|string|max:100');
        
        $form->text('village', __('Village'))
             ->rules('nullable|string|max:100');
        
        // Parent/Guardian Information
        $form->divider('Parent/Guardian Information');
        
        $form->text('parent_name', __('Parent/Guardian Name'))
             ->rules('nullable|string|max:200');
        
        $form->text('parent_relationship', __('Relationship'))
             ->rules('nullable|string|max:50');
        
        $form->text('parent_phone', __('Parent Phone'))
             ->rules('nullable|string|max:20');
        
        $form->email('parent_email', __('Parent Email'))
             ->rules('nullable|email|max:255');
        
        $form->textarea('parent_address', __('Parent Address'))
             ->rows(3)
             ->rules('nullable|string|max:500');
        
        // Previous Education
        $form->divider('Previous Education');
        
        $form->text('previous_school', __('Previous School'))
             ->rules('nullable|string|max:200');
        
        $form->text('previous_class', __('Previous Class'))
             ->rules('nullable|string|max:50');
        
        $form->text('year_completed', __('Year Completed'))
             ->rules('nullable|string|max:4');
        
        // Application Details
        $form->divider('Application Details');
        
        $form->text('applying_for_class', __('Applying For Class'))
             ->required()
             ->rules('required|string|max:100');
        
        $form->textarea('special_needs', __('Special Needs/Requirements'))
             ->rows(3)
             ->rules('nullable|string|max:1000');
        
        // Admin Review
        $form->divider('Administrative Review');
        
        $form->textarea('admin_notes', __('Admin Notes'))
             ->rows(4)
             ->rules('nullable|string|max:2000')
             ->help('Internal notes about this application');
        
        $form->textarea('rejection_reason', __('Rejection Reason'))
             ->rows(4)
             ->rules('nullable|string|max:1000')
             ->help('Required when rejecting an application');
        
        // Timestamps (display only)
        $form->divider('Timeline');
        
        $form->display('created_at', __('Created At'));
        $form->display('submitted_at', __('Submitted At'));
        $form->display('reviewed_at', __('Reviewed At'));
        $form->display('completed_at', __('Completed At'));
        
        $form->disableCreatingCheck();
        $form->disableViewCheck();
        $form->disableReset();
        
        return $form;
    }
}
