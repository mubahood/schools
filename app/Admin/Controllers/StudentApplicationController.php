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
            $actions->disableEdit();
            
            // Add prominent "View Details" button
            $viewUrl = admin_url("student-applications/{$actions->getKey()}");
            $actions->append("<a href='$viewUrl' class='btn btn-sm btn-info' title='View Full Details' style='margin-right: 5px;'>
                <i class='fa fa-eye'></i> View Details
            </a>");
            
            // Add review button for submitted/under_review applications
            if (in_array($actions->row->status, ['submitted', 'under_review'])) {
                $reviewUrl = admin_url("student-applications/{$actions->getKey()}/review");
                $actions->append("<a href='$reviewUrl' class='btn btn-sm btn-primary' title='Review Application' style='margin-right: 5px;'>
                    <i class='fa fa-check-circle'></i> Review
                </a>");
            }
            
            // Add accept/reject quick actions
            if ($actions->row->status === 'submitted' || $actions->row->status === 'under_review') {
                $actions->append("
                    <a href='javascript:void(0);' 
                       class='btn btn-sm btn-success accept-application' 
                       data-id='{$actions->getKey()}'
                       title='Accept Application'
                       style='margin-right: 3px;'>
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
        
        // Disable edit/delete buttons in show view
        $show->panel()->tools(function ($tools) {
            $tools->disableEdit();
            $tools->disableDelete();
        });
        
        // ========== APPLICATION INFORMATION ==========
        $show->panel()
             ->title('<i class="fa fa-file-text"></i> Application Information')
             ->style('primary');
        
        $show->field('application_number', __('Application Number'))
             ->as(function($number) {
                 return "<span style='font-size: 18px; font-weight: bold; color: #0d6efd;'>$number</span>";
             });
        
        $show->field('status', __('Status'))->as(function($status) {
            $badges = [
                'draft' => ['secondary', 'Draft', 'clock'],
                'submitted' => ['primary', 'Submitted', 'paper-plane'],
                'under_review' => ['info', 'Under Review', 'search'],
                'accepted' => ['success', 'Accepted', 'check-circle'],
                'rejected' => ['danger', 'Rejected', 'times-circle'],
                'cancelled' => ['warning', 'Cancelled', 'ban']
            ];
            $badge = $badges[$status] ?? ['secondary', ucwords(str_replace('_', ' ', $status)), 'circle'];
            return "<span class='badge badge-{$badge[0]}' style='font-size: 14px; padding: 8px 12px;'>
                        <i class='fa fa-{$badge[2]}'></i> {$badge[1]}
                    </span>";
        });
        
        $show->field('progress_percentage', __('Completion Progress'))
             ->as(function($progress) {
                 $color = $progress < 30 ? 'danger' : ($progress < 70 ? 'warning' : 'success');
                 return "<div class='progress' style='height: 25px;'>
                     <div class='progress-bar bg-$color progress-bar-striped' role='progressbar' 
                          style='width: {$progress}%' 
                          aria-valuenow='$progress' aria-valuemin='0' aria-valuemax='100'>
                         <strong>{$progress}%</strong>
                     </div>
                 </div>";
             });
        
        $show->field('selectedEnterprise.name', __('Selected School'))
             ->as(function($name) {
                 return $name ? "<strong style='color: #198754;'>$name</strong>" : '<em class="text-muted">Not selected</em>';
             });
        
        $show->field('applying_for_class', __('Applying For Class'))
             ->as(function($class) {
                 return $class ? "<span class='badge badge-info' style='font-size: 13px; padding: 6px 10px;'>$class</span>" : '-';
             });
        
        // ========== PERSONAL INFORMATION ==========
        $show->panel()
             ->title('<i class="fa fa-user"></i> Personal Information')
             ->style('info');
        
        $show->field('full_name', __('Full Name'))
             ->as(function() {
                 $fullName = trim($this->first_name . ' ' . ($this->middle_name ?? '') . ' ' . $this->last_name);
                 return "<strong style='font-size: 16px;'>$fullName</strong>";
             });
        
        $show->field('date_of_birth', __('Date of Birth'))
             ->as(function($dob) {
                 if (!$dob) return '-';
                 $age = Carbon::parse($dob)->age;
                 return Carbon::parse($dob)->format('F d, Y') . " <span class='badge badge-secondary'>Age: $age years</span>";
             });
        
        $show->field('gender', __('Gender'))
             ->as(function($gender) {
                 if (!$gender) return '-';
                 $icon = strtolower($gender) === 'male' ? 'male' : 'female';
                 $color = strtolower($gender) === 'male' ? '#0d6efd' : '#d63384';
                 return "<i class='fa fa-$icon' style='color: $color; font-size: 18px;'></i> <strong>" . ucfirst(strtolower($gender)) . "</strong>";
             });
        
        $show->field('nationality', __('Nationality'));
        $show->field('religion', __('Religion'));
        
        // ========== CONTACT INFORMATION ==========
        $show->panel()
             ->title('<i class="fa fa-phone"></i> Contact Information')
             ->style('success');
        
        $show->field('email', __('Email Address'))
             ->as(function($email) {
                 return $email ? "<a href='mailto:$email' style='font-size: 14px;'><i class='fa fa-envelope'></i> $email</a>" : '-';
             });
        
        $show->field('phone_number', __('Primary Phone'))
             ->as(function($phone) {
                 return $phone ? "<a href='tel:$phone'><i class='fa fa-phone'></i> $phone</a>" : '-';
             });
        
        $show->field('phone_number_2', __('Alternative Phone'))
             ->as(function($phone) {
                 return $phone ? "<a href='tel:$phone'><i class='fa fa-phone'></i> $phone</a>" : '-';
             });
        
        $show->field('home_address', __('Home Address'))
             ->as(function($address) {
                 return $address ? nl2br(htmlspecialchars($address)) : '-';
             });
        
        $show->field('district', __('District'));
        $show->field('city', __('City'));
        $show->field('village', __('Village'));
        
        // ========== PARENT/GUARDIAN INFORMATION ==========
        $show->panel()
             ->title('<i class="fa fa-users"></i> Parent/Guardian Information')
             ->style('warning');
        
        $show->field('parent_name', __('Parent/Guardian Name'))
             ->as(function($name) {
                 return $name ? "<strong>$name</strong>" : '-';
             });
        
        $show->field('parent_relationship', __('Relationship'));
        
        $show->field('parent_phone', __('Parent Phone'))
             ->as(function($phone) {
                 return $phone ? "<a href='tel:$phone'><i class='fa fa-phone'></i> $phone</a>" : '-';
             });
        
        $show->field('parent_email', __('Parent Email'))
             ->as(function($email) {
                 return $email ? "<a href='mailto:$email'><i class='fa fa-envelope'></i> $email</a>" : '-';
             });
        
        $show->field('parent_address', __('Parent Address'))
             ->as(function($address) {
                 return $address ? nl2br(htmlspecialchars($address)) : '-';
             });
        
        // ========== PREVIOUS EDUCATION ==========
        $show->panel()
             ->title('<i class="fa fa-graduation-cap"></i> Previous Education')
             ->style('secondary');
        
        $show->field('previous_school', __('Previous School'));
        $show->field('previous_class', __('Previous Class'));
        $show->field('year_completed', __('Year Completed'));
        
        // ========== ADDITIONAL INFORMATION ==========
        $show->panel()
             ->title('<i class="fa fa-info-circle"></i> Additional Information')
             ->style('default');
        
        $show->field('special_needs', __('Special Needs/Requirements'))
             ->as(function($needs) {
                 return $needs ? nl2br(htmlspecialchars($needs)) : '<em class="text-muted">None specified</em>';
             });
        
        $show->divider();
        
        // ========== SUPPORTING DOCUMENTS ==========
        $show->field('attachments', __('Supporting Documents'))
             ->as(function($attachments) {
                 if (empty($attachments) || !is_array($attachments)) {
                     return '<div class="alert alert-info">
                         <i class="fa fa-info-circle"></i> No documents have been attached to this application.
                     </div>';
                 }
                 
                 $html = '<div class="table-responsive" style="margin-top: 10px;">
                     <table class="table table-bordered table-hover table-striped">
                         <thead style="background-color: #f8f9fa;">
                             <tr>
                                 <th width="40" class="text-center">#</th>
                                 <th><i class="fa fa-file"></i> Document Name</th>
                                 <th width="100"><i class="fa fa-hdd-o"></i> Size</th>
                                 <th width="150"><i class="fa fa-calendar"></i> Uploaded</th>
                                 <th width="120" class="text-center"><i class="fa fa-cog"></i> Actions</th>
                             </tr>
                         </thead>
                         <tbody>';
                 
                 foreach ($attachments as $index => $doc) {
                     $num = $index + 1;
                     $name = htmlspecialchars($doc['name'] ?? 'Unknown Document');
                     $size = number_format(($doc['size'] ?? 0) / 1024, 2) . ' KB';
                     $date = isset($doc['uploaded_at']) ? Carbon::parse($doc['uploaded_at'])->format('M d, Y H:i') : '-';
                     $path = $doc['path'] ?? '';
                     $url = $path ? asset('storage/' . $path) : '#';
                     
                     // Get file icon and color based on type
                     $type = $doc['type'] ?? '';
                     $icon = 'file-o';
                     $iconColor = '#6c757d';
                     $badge = 'secondary';
                     
                     if (str_contains($type, 'pdf')) {
                         $icon = 'file-pdf-o';
                         $iconColor = '#dc3545';
                         $badge = 'danger';
                     } elseif (str_contains($type, 'image')) {
                         $icon = 'file-image-o';
                         $iconColor = '#198754';
                         $badge = 'success';
                     } elseif (str_contains($type, 'word') || str_contains($type, 'doc')) {
                         $icon = 'file-word-o';
                         $iconColor = '#0d6efd';
                         $badge = 'primary';
                     } elseif (str_contains($type, 'excel') || str_contains($type, 'sheet')) {
                         $icon = 'file-excel-o';
                         $iconColor = '#198754';
                         $badge = 'success';
                     }
                     
                     $html .= "
                         <tr>
                             <td class='text-center'><strong>$num</strong></td>
                             <td>
                                 <i class='fa fa-$icon' style='color: $iconColor; font-size: 16px;'></i> 
                                 <strong>$name</strong>
                             </td>
                             <td><span class='badge badge-$badge'>$size</span></td>
                             <td><small><i class='fa fa-clock-o'></i> $date</small></td>
                             <td class='text-center'>
                                 <a href='$url' target='_blank' class='btn btn-sm btn-primary' title='View/Download'>
                                     <i class='fa fa-eye'></i> View
                                 </a>
                                 <a href='$url' download class='btn btn-sm btn-success' title='Download'>
                                     <i class='fa fa-download'></i>
                                 </a>
                             </td>
                         </tr>
                     ";
                 }
                 
                 $html .= '</tbody></table></div>';
                 $html .= '<p class="text-muted" style="margin-top: 10px;"><i class="fa fa-paperclip"></i> Total Documents: <strong>' . count($attachments) . '</strong></p>';
                 return $html;
             });
        
        $show->divider();
        
        // ========== APPLICATION TIMELINE ==========
        $show->panel()
             ->title('<i class="fa fa-clock-o"></i> Application Timeline')
             ->style('default');
        
        $show->field('created_at', __('Application Started'))
             ->as(function($date) {
                 return "<i class='fa fa-calendar'></i> " . Carbon::parse($date)->format('F d, Y') . 
                        " <small class='text-muted'>at " . Carbon::parse($date)->format('h:i A') . "</small>";
             });
        
        $show->field('submitted_at', __('Submitted'))
             ->as(function($date) {
                 if (!$date) return '<em class="text-muted">Not yet submitted</em>';
                 return "<i class='fa fa-paper-plane'></i> " . Carbon::parse($date)->format('F d, Y') . 
                        " <small class='text-muted'>at " . Carbon::parse($date)->format('h:i A') . "</small>";
             });
        
        $show->field('reviewed_at', __('Reviewed'))
             ->as(function($date) {
                 if (!$date) return '<em class="text-muted">Not yet reviewed</em>';
                 return "<i class='fa fa-search'></i> " . Carbon::parse($date)->format('F d, Y') . 
                        " <small class='text-muted'>at " . Carbon::parse($date)->format('h:i A') . "</small>";
             });
        
        $show->field('completed_at', __('Completed'))
             ->as(function($date) {
                 if (!$date) return '<em class="text-muted">Not yet completed</em>';
                 return "<i class='fa fa-check-circle'></i> " . Carbon::parse($date)->format('F d, Y') . 
                        " <small class='text-muted'>at " . Carbon::parse($date)->format('h:i A') . "</small>";
             });
        
        // ========== ADMIN REVIEW SECTION ==========
        $application = StudentApplication::find($id);
        if ($application && ($application->reviewed_by || $application->admin_notes || $application->rejection_reason)) {
            $show->divider();
            
            $show->panel()
                 ->title('<i class="fa fa-user-secret"></i> Administrative Review')
                 ->style('info');
            
            if ($application->reviewed_by) {
                $show->field('reviewer.name', __('Reviewed By'))
                     ->as(function($name) {
                         return $name ? "<strong style='color: #0d6efd;'>$name</strong>" : '-';
                     });
            }
            
            $show->field('admin_notes', __('Admin Notes'))
                 ->as(function($notes) {
                     return $notes ? nl2br(htmlspecialchars($notes)) : '<em class="text-muted">No notes</em>';
                 });
            
            $show->field('rejection_reason', __('Rejection Reason'))
                 ->as(function($reason) {
                     if (!$reason) return '<em class="text-muted">N/A</em>';
                     return '<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> ' . nl2br(htmlspecialchars($reason)) . '</div>';
                 });
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
     * Note: Only allows editing status and admin notes
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new StudentApplication());
        
        // Configure form tools
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });
        
        $form->footer(function ($footer) {
            $footer->disableReset();
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });
        
        // Applications come from public portal - disable creation
        if ($form->isCreating()) {
            return redirect()
                ->back()
                ->with('error', 'Student applications cannot be created from admin panel. They must be submitted through the public application portal.');
        }
        
        // ========== APPLICATION OVERVIEW (Display Only) ==========
        $form->html('<div class="alert alert-info">
            <i class="fa fa-info-circle"></i> <strong>Note:</strong> Most application fields are read-only and can only be edited by the applicant. 
            You can update the status and add administrative notes below.
        </div>');
        
        $form->divider('<i class="fa fa-file-text"></i> Application Overview');
        
        $form->display('application_number', __('Application Number'))
             ->with(function ($value) {
                 return "<strong style='color: #0d6efd; font-size: 16px;'>$value</strong>";
             });
        
        $form->display('full_name', __('Applicant Name'))
             ->with(function () {
                 $fullName = trim($this->first_name . ' ' . ($this->middle_name ?? '') . ' ' . $this->last_name);
                 return "<strong>$fullName</strong>";
             });
        
        $form->display('email', __('Email'))
             ->with(function ($value) {
                 return $value ? "<a href='mailto:$value'>$value</a>" : '-';
             });
        
        $form->display('phone_number', __('Phone'))
             ->with(function ($value) {
                 return $value ? "<a href='tel:$value'>$value</a>" : '-';
             });
        
        $form->display('applying_for_class', __('Applying For Class'))
             ->with(function ($value) {
                 return $value ? "<span class='badge badge-info' style='font-size: 13px; padding: 6px 12px;'>$value</span>" : '-';
             });
        
        $form->display('progress_percentage', __('Application Progress'))
             ->with(function ($value) {
                 $color = $value < 30 ? 'danger' : ($value < 70 ? 'warning' : 'success');
                 return "<div class='progress' style='height: 25px;'>
                     <div class='progress-bar bg-$color' role='progressbar' style='width: {$value}%'>
                         <strong>{$value}%</strong>
                     </div>
                 </div>";
             });
        
        // ========== STATUS MANAGEMENT (Editable) ==========
        $form->divider('<i class="fa fa-cog"></i> Status Management');
        
        $form->select('status', __('Application Status'))
             ->options([
                 'draft' => '📝 Draft',
                 'submitted' => '📤 Submitted',
                 'under_review' => '🔍 Under Review',
                 'accepted' => '✅ Accepted',
                 'rejected' => '❌ Rejected',
                 'cancelled' => '🚫 Cancelled'
             ])
             ->required()
             ->rules('required|in:draft,submitted,under_review,accepted,rejected,cancelled')
             ->help('Update the application status. Changing to "Accepted" will create a student account.')
             ->default('submitted');
        
        // ========== ADMINISTRATIVE NOTES (Editable) ==========
        $form->divider('<i class="fa fa-sticky-note"></i> Administrative Notes');
        
        $form->textarea('admin_notes', __('Internal Notes'))
             ->rows(5)
             ->rules('nullable|string|max:2000')
             ->help('Add internal notes about this application. These notes are not visible to the applicant.')
             ->placeholder('Enter any internal notes, observations, or comments about this application...');
        
        $form->textarea('rejection_reason', __('Rejection Reason'))
             ->rows(4)
             ->rules('nullable|string|max:1000')
             ->help('⚠️ Required when status is "Rejected". This message will be sent to the applicant.')
             ->placeholder('Clearly explain why this application was rejected...');
        
        // ========== TIMELINE (Display Only) ==========
        $form->divider('<i class="fa fa-clock-o"></i> Application Timeline');
        
        $form->display('created_at', __('Started'))
             ->with(function ($value) {
                 return $value ? Carbon::parse($value)->format('F d, Y h:i A') : '-';
             });
        
        $form->display('submitted_at', __('Submitted'))
             ->with(function ($value) {
                 return $value ? Carbon::parse($value)->format('F d, Y h:i A') : '<em class="text-muted">Not yet submitted</em>';
             });
        
        $form->display('reviewed_at', __('Reviewed'))
             ->with(function ($value) {
                 return $value ? Carbon::parse($value)->format('F d, Y h:i A') : '<em class="text-muted">Not yet reviewed</em>';
             });
        
        $form->display('updated_at', __('Last Updated'))
             ->with(function ($value) {
                 return Carbon::parse($value)->format('F d, Y h:i A');
             });
        
        // Validation on save
        $form->saving(function (Form $form) {
            // If status is rejected, require rejection_reason
            if ($form->status === 'rejected' && empty($form->rejection_reason)) {
                return back()->withErrors(['rejection_reason' => 'Rejection reason is required when rejecting an application.']);
            }
            
            // Set reviewed_at when status changes to under_review, accepted, or rejected
            if (in_array($form->status, ['under_review', 'accepted', 'rejected'])) {
                if (!$form->model()->reviewed_at) {
                    $form->reviewed_at = now();
                    $form->reviewed_by = Admin::user()->id;
                }
            }
        });
        
        return $form;
    }
}
