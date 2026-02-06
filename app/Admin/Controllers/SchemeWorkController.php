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
        
        // Disable batch actions
        $grid->disableBatchActions();
        $grid->disableExport();
        
        // Custom create button that goes to scheme items
        $grid->disableCreateButton();
        $grid->tools(function ($tools) {
            $tools->append('<a href="' . admin_url('schems-work-items/create') . '" class="btn btn-sm btn-success">
                <i class="fa fa-plus"></i> Create New Scheme Item
            </a>');
        });
        
        // Header with active term information
        if ($active_term) {
            $grid->header(function () use ($active_term) {
                return '<div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> 
                    <strong>Active Term:</strong> ' . $active_term->name_text . ' | 
                    <strong>Academic Year:</strong> ' . $active_term->academic_year->name . '
                </div>';
            });
        }

        // Filter configuration
        $grid->filter(function ($filter) use ($u, $active_year, $active_term) {
            $filter->disableIdFilter();
            
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
                if (!$active_term) {
                    return; // Skip filter if no active term
                }
                $status = $this->input;
                if ($status == 'completed') {
                    $query->whereHas('scheme_work_items', function ($q) use ($active_term) {
                        $q->where('term_id', $active_term->id)
                          ->where('teacher_status', 'Conducted');
                    });
                } elseif ($status == 'pending') {
                    $query->whereHas('scheme_work_items', function ($q) use ($active_term) {
                        $q->where('term_id', $active_term->id)
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
                if ($c == null) return '<span class="label label-default">N/A</span>';
                return '<span class="label label-primary">' . $c->name_text . '</span>';
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
            ->display(function () use ($active_term) {
                if (!$active_term) {
                    return '<span class="text-muted">No active term</span>';
                }

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

                $percentage = $total > 0 ? round(($conducted / $total) * 100, 1) : 0;

                return '
                    <div style="font-size: 11px;">
                        <span class="badge" style="background: #3c8dbc;">' . $total . ' Total</span>
                        <span class="badge" style="background: #00a65a;">' . $conducted . ' Done</span>
                        <span class="badge" style="background: #f39c12;">' . $pending . ' Pending</span>
                        <span class="badge" style="background: #dd4b39;">' . $skipped . ' Skipped</span>
                        <br><small class="text-primary"><strong>' . $percentage . '% Completed</strong></small>
                    </div>
                ';
            });

        // Actions
        $grid->column('actions', __('Actions'))
            ->display(function () {
                $viewUrl = admin_url('schems-work-items?subject_id=' . $this->id);
                $printUrl = url('scheme-of-work-print?id=' . $this->id);
                
                return '
                    <a href="' . $viewUrl . '" class="btn btn-sm btn-primary" title="View Items">
                        <i class="fa fa-list"></i> View Items
                    </a>
                    <a href="' . $printUrl . '" target="_blank" class="btn btn-sm btn-success" title="Print">
                        <i class="fa fa-print"></i> Print
                    </a>
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
