<?php

namespace App\Admin\Controllers;

use App\Models\AcademicClass;
use App\Models\AcademicYear;
use App\Models\SchemWorkItem;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SchemeWorkController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Scheme of Work Management';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $u = Admin::user();
        
        // Get active academic year
        $active_year = AcademicYear::where([
            'enterprise_id' => $u->enterprise_id,
            'is_active' => 1
        ])->first();

        if (!$active_year) {
            admin_error('Error', 'No active academic year found. Please activate an academic year first.');
            return redirect(admin_url('academic-years'));
        }

        // Get active term
        $active_term = Term::where([
            'enterprise_id' => $u->enterprise_id,
            'is_active' => 1
        ])->first();

        $grid = new Grid(new Subject());
        
        // Get enterprise primary color
        $primaryColor = $u->ent->color ?? '#337ab7';
        
        // Add custom CSS with dynamic enterprise colors
        Admin::css('/css/scheme-work-custom.css');
        Admin::style("
            :root {
                --enterprise-primary: {$primaryColor};
                --enterprise-dark: {$primaryColor}dd;
                --enterprise-light: {$primaryColor}99;
                --enterprise-lighter: {$primaryColor}66;
                --enterprise-pale: {$primaryColor}33;
            }
            .class-badge, .scheme-status-badge.badge-total, .btn-add {
                background-color: {$primaryColor} !important;
                border-color: {$primaryColor} !important;
            }
            .scheme-status-badge.badge-done, .btn-view {
                background-color: {$primaryColor}dd !important;
                border-color: {$primaryColor}dd !important;
            }
            .scheme-status-badge.badge-pending, .btn-print {
                background-color: {$primaryColor}99 !important;
                border-color: {$primaryColor}99 !important;
            }
            .scheme-status-badge.badge-skipped {
                background-color: {$primaryColor}66 !important;
                border-color: {$primaryColor}66 !important;
            }
        ");
        
        // Disable batch actions
        $grid->disableBatchActions();
        $grid->disableExport();
        $grid->disableCreateButton();
        
        // Header with term information
        $grid->header(function () use ($active_term) {
            // Check if term filter is applied
            $filtered_term_id = request()->get('term_filter');
            $display_term = $filtered_term_id ? Term::find($filtered_term_id) : $active_term;
            
            if (!$display_term) {
                return '<div class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle"></i> 
                    <strong>Warning:</strong> No term selected. Please select a term from the filters.
                </div>';
            }
            
            $badge_class = $display_term->is_active ? 'enterprise-badge-primary' : 'enterprise-badge-light';
            $status_text = $display_term->is_active ? 'Active' : 'Previous';
            
            return '<div class="alert alert-info">
                <i class="fa fa-info-circle"></i> 
                <strong>Viewing Term:</strong> ' . $display_term->name_text . ' 
                <span class="' . $badge_class . '" style="font-size:10px; padding:2px 6px; margin-left:5px;">' . $status_text . '</span> | 
                <strong>Academic Year:</strong> ' . $display_term->academic_year->name . '
            </div>';
        });

        // Filter configuration
        $grid->filter(function ($filter) use ($u, $active_year, $active_term) {
            $filter->disableIdFilter();
            
            // Filter by academic term
            $filter->equal('term_filter', 'Academic Term')
                ->select(Term::where([
                    'enterprise_id' => $u->enterprise_id
                ])->orderBy('is_active', 'desc')
                  ->orderBy('id', 'desc')
                  ->get()
                  ->pluck('name_text', 'id'))
                ->default($active_term ? $active_term->id : null);
            
            // Filter by class
            $filter->equal('academic_class_id', 'Class')
                ->select(AcademicClass::where([
                    'enterprise_id' => $u->enterprise_id,
                    'academic_year_id' => $active_year->id
                ])->orderBy('name')->pluck('name', 'id'));

            // Filter by teacher
            $filter->equal('subject_teacher', 'Main Teacher')
                ->select(User::where([
                    'enterprise_id' => $u->enterprise_id,
                    'user_type' => 'employee'
                ])->orderBy('first_name')->get()->pluck('name', 'id'));

            // Filter by completion status
            $filter->where(function ($query) use ($active_term) {
                // Get the filtered term or use active term
                $filtered_term_id = request()->get('term_filter');
                $display_term = $filtered_term_id ? Term::find($filtered_term_id) : $active_term;
                
                if (!$display_term) {
                    return; // Skip filter if no term available
                }
                
                $status = $this->input;
                if ($status == 'completed') {
                    $query->whereHas('scheme_work_items', function ($q) use ($display_term) {
                        $q->where('term_id', $display_term->id)
                          ->where('teacher_status', 'Conducted');
                    });
                } elseif ($status == 'pending') {
                    $query->whereHas('scheme_work_items', function ($q) use ($display_term) {
                        $q->where('term_id', $display_term->id)
                          ->where('teacher_status', 'Pending');
                    });
                }
            }, 'Completion Status')->select([
                'completed' => 'Has Completed Items',
                'pending' => 'Has Pending Items'
            ]);
        });

        // Quick search
        $grid->quickSearch('subject_name', 'code')->placeholder('Search by subject name or code');

        // Model query
        $conds = [
            'enterprise_id' => $u->enterprise_id,
            'academic_year_id' => $active_year->id
        ];

        $grid->model()
            ->where($conds)
            ->orderBy('academic_class_id', 'asc')
            ->orderBy('subject_name', 'asc');

        // Grid columns
        $grid->column('id', __('ID'))->sortable()->hide();
        
        $grid->column('academic_class_id', __('Class'))
            ->display(function ($class_id) {
                $c = AcademicClass::find($class_id);
                if ($c == null) return '<span class=\"class-badge\">N/A</span>';
                return '<span class=\"class-badge\">' . $c->name_text . '</span>';
            })->sortable();

        $grid->column('subject_name', __('Subject'))
            ->display(function ($name) {
                return '<strong>' . $name . '</strong>';
            })->sortable();

        $grid->column('code', __('Code'))
            ->display(function ($code) {
                return $code ?: '<span class="text-muted">-</span>';
            })->sortable();

        $grid->column('subject_teacher', __('Main Teacher'))
            ->display(function ($teacher_id) {
                $t = User::find($teacher_id);
                if ($t == null) return '<span class="text-muted">Not Assigned</span>';
                return '<i class="fa fa-user"></i> ' . $t->name;
            })->sortable();

        // Scheme work statistics
        $grid->column('statistics', __('Scheme Work Statistics'))
            ->display(function () use ($active_term, $u) {
                // Check if term filter is applied, otherwise use active term
                $filtered_term_id = request()->get('term_filter');
                $display_term = $filtered_term_id ? Term::find($filtered_term_id) : $active_term;
                
                if (!$display_term) {
                    return '<span class="text-muted">No term selected</span>';
                }

                $total = SchemWorkItem::where([
                    'subject_id' => $this->id,
                    'term_id' => $display_term->id
                ])->count();

                $conducted = SchemWorkItem::where([
                    'subject_id' => $this->id,
                    'term_id' => $display_term->id,
                    'teacher_status' => 'Conducted'
                ])->count();

                $pending = SchemWorkItem::where([
                    'subject_id' => $this->id,
                    'term_id' => $display_term->id,
                    'teacher_status' => 'Pending'
                ])->count();

                $skipped = SchemWorkItem::where([
                    'subject_id' => $this->id,
                    'term_id' => $display_term->id,
                    'teacher_status' => 'Skipped'
                ])->count();

                $percentage = $total > 0 ? round(($conducted / $total) * 100, 1) : 0;

                // Show which term's data is being displayed
                $termBadge = '<span class="enterprise-badge-primary" style="font-size:10px;">' . $display_term->name_text . '</span><br>';
                
                return '
                    <div style="font-size: 11px;">
                        ' . $termBadge . '
                        <span class="scheme-status-badge badge-total">' . $total . ' Total</span>
                        <span class="scheme-status-badge badge-done">' . $conducted . ' Done</span>
                        <span class="scheme-status-badge badge-pending">' . $pending . ' Pending</span>
                        <span class="scheme-status-badge badge-skipped">' . $skipped . ' Skipped</span>
                        <br><small><strong>' . $percentage . '% Completed</strong></small>
                    </div>
                ';
            });

        // Actions
        $grid->column('actions', __('Actions'))
            ->display(function () {
                $viewUrl = admin_url('schems-work-items?subject_id=' . $this->id);
                $createUrl = admin_url('schems-work-items/create?subject_id=' . $this->id);
                $printUrl = url('scheme-of-work-print?id=' . $this->id);
                
                return '
                    <div class="scheme-work-actions">
                        <a href="' . $createUrl . '" class="btn btn-sm btn-add" title="Add New Item">
                            <i class="fa fa-plus"></i> Add
                        </a>
                        <a href="' . $viewUrl . '" class="btn btn-sm btn-view" title="View Items">
                            <i class="fa fa-list"></i> View
                        </a>
                        <a href="' . $printUrl . '" target="_blank" class="btn btn-sm btn-print" title="Print">
                            <i class="fa fa-print"></i>
                        </a>
                    </div>
                ';
            });

        $grid->disableActions();

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
        $show = new Show(Subject::findOrFail($id));
        
        $u = Admin::user();
        $active_term = Term::where([
            'enterprise_id' => $u->enterprise_id,
            'is_active' => 1
        ])->first();

        $show->panel()->tools(function ($tools) use ($id) {
            $tools->append('<a href="' . url('scheme-of-work-print?id=' . $id) . '" target="_blank" class="btn btn-sm btn-success"><i class="fa fa-print"></i> Print Scheme</a>');
        });

        $show->field('subject_name', __('Subject Name'));
        $show->field('code', __('Code'));
        
        $show->field('academic_class_id', __('Class'))->as(function ($class_id) {
            $c = AcademicClass::find($class_id);
            return $c ? $c->name_text : 'N/A';
        });

        $show->field('subject_teacher', __('Main Teacher'))->as(function ($teacher_id) {
            $t = User::find($teacher_id);
            return $t ? $t->name : 'Not Assigned';
        });

        $show->field('teacher_1', __('Additional Teacher 1'))->as(function ($teacher_id) {
            $t = User::find($teacher_id);
            return $t ? $t->name : 'Not Assigned';
        });

        $show->field('teacher_2', __('Additional Teacher 2'))->as(function ($teacher_id) {
            $t = User::find($teacher_id);
            return $t ? $t->name : 'Not Assigned';
        });

        if ($active_term) {
            $show->divider();
            $show->field('scheme_statistics', __('Scheme Work Statistics (Current Term)'))->unescape()->as(function () use ($active_term) {
                $total = SchemWorkItem::where([
                    'subject_id' => $this->id,
                    'term_id' => $active_term->id
                ])->count();

                $conducted = SchemWorkItem::where([
                    'subject_id' => $this->id,
                    'term_id' => $active_term->id,
                    'teacher_status' => 'Conducted'
                ])->count();

                $pending = SchemWorkItem::where([
                    'subject_id' => $this->id,
                    'term_id' => $active_term->id,
                    'teacher_status' => 'Pending'
                ])->count();

                $skipped = SchemWorkItem::where([
                    'subject_id' => $this->id,
                    'term_id' => $active_term->id,
                    'teacher_status' => 'Skipped'
                ])->count();

                return "
                    <table class='table table-bordered'>
                        <tr><th>Total Items</th><td>$total</td></tr>
                        <tr><th>Conducted</th><td><span class='label label-success'>$conducted</span></td></tr>
                        <tr><th>Pending</th><td><span class='label label-warning'>$pending</span></td></tr>
                        <tr><th>Skipped</th><td><span class='label label-danger'>$skipped</span></td></tr>
                    </table>
                ";
            });
        }

        $show->field('details', __('Details'));
        $show->field('created_at', __('Created At'));
        $show->field('updated_at', __('Updated At'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        // This controller is read-only. Subjects are managed in the Subjects section.
        // Redirect to the subjects management page.
        admin_warning('Information', 'Subjects are managed in the Subjects section. This is a read-only view for scheme work management.');
        return redirect(admin_url('subjects'));
    }
}
