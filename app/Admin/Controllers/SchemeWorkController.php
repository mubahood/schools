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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
        $grid->disableCreateButton();
        
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
        Admin::style($this->getPopupStyle());
        
        // Disable batch actions
        $grid->disableBatchActions();
        $popupConfig = [
            'defaultTermId' => $active_term ? (int) $active_term->id : 0,
            'storeUrl' => admin_url('scheme-works/add-item-ajax'),
        ];
        Admin::script('window.SCHEME_POPUP_CONFIG = ' . json_encode($popupConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ';');
        Admin::js('/js/scheme-work-popup.js?v=20260422-1');
        Admin::script(<<<'JS'
            (function () {
                // Debugbar may call hljs using a newer signature while another package exposes an older hljs API.
                function escapeHtml(s) {
                    return String(s)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;');
                }

                function patchDebugbarHighlight() {
                    if (!window.PhpDebugBar || !PhpDebugBar.Widgets || !PhpDebugBar.Widgets.highlight) {
                        return false;
                    }
                    if (PhpDebugBar.Widgets.__schemeHljsPatched) {
                        return true;
                    }

                    var original = PhpDebugBar.Widgets.highlight;
                    PhpDebugBar.Widgets.highlight = function (code, lang) {
                        if (typeof code === 'string' && typeof window.hljs !== 'undefined' && lang) {
                            try {
                                if (typeof hljs.getLanguage === 'function' && !hljs.getLanguage(lang)) {
                                    return escapeHtml(code);
                                }

                                // Try highlight.js new API first.
                                try {
                                    if (typeof hljs.highlight === 'function') {
                                        var modern = hljs.highlight(code, { language: lang });
                                        if (modern && modern.value) return modern.value;
                                    }
                                } catch (e1) {
                                    // Fallback for old highlight.js signature: highlight(lang, code)
                                    try {
                                        var legacy = hljs.highlight(lang, code);
                                        if (legacy && legacy.value) return legacy.value;
                                    } catch (e2) {
                                        return escapeHtml(code);
                                    }
                                }
                            } catch (e) {
                                return escapeHtml(code);
                            }
                        }

                        try {
                            return original(code, lang);
                        } catch (e3) {
                            return typeof code === 'string' ? escapeHtml(code) : code;
                        }
                    };

                    PhpDebugBar.Widgets.__schemeHljsPatched = true;
                    return true;
                }

                if (!patchDebugbarHighlight()) {
                    var attempts = 0;
                    var t = setInterval(function () {
                        attempts++;
                        if (patchDebugbarHighlight() || attempts > 30) {
                            clearInterval(t);
                        }
                    }, 120);
                }
            })();
        JS);
        
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

        // Model query — privileged users see all subjects, teachers see only theirs
        $isPrivileged = $u->isRole('admin') || $u->isRole('dos') || $u->isRole('hm');

        $grid->model()
            ->where([
                'enterprise_id' => $u->enterprise_id,
                'academic_year_id' => $active_year->id
            ])
            ->when(!$isPrivileged, function ($query) use ($u) {
                $query->where(function ($q) use ($u) {
                    $q->where('subject_teacher', $u->id)
                      ->orWhere('teacher_1', $u->id)
                      ->orWhere('teacher_2', $u->id)
                      ->orWhere('teacher_3', $u->id);
                });
            })
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
            })->sortable()->hide();

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

                return '
                    <div class="scheme-stat-block" id="scheme-stat-' . $this->id . '">
                        <span class="scheme-term-chip">' . $display_term->name_text . '</span>
                        <span class="scheme-status-badge badge-total"><span class="js-stat-total">' . $total . '</span> Total</span>
                        <span class="scheme-status-badge badge-done"><span class="js-stat-done">' . $conducted . '</span> Done</span>
                        <span class="scheme-status-badge badge-pending"><span class="js-stat-pending">' . $pending . '</span> Pending</span>
                        <span class="scheme-status-badge badge-skipped"><span class="js-stat-skipped">' . $skipped . '</span> Skipped</span>
                        <span class="scheme-percent"><span class="js-stat-percent">' . $percentage . '</span>%</span>
                    </div>
                ';
            });

        // Actions
        $grid->column('actions', __('Actions'))
            ->display(function () {
                $viewUrl = admin_url('schems-work-items?subject_id=' . $this->id);
                $printUrl = url('scheme-of-work-print?id=' . $this->id);
                
                return '
                    <div class="scheme-work-actions">
                        <a href="#" class="btn btn-sm btn-add js-open-scheme-popup" data-subject-id="' . $this->id . '" data-subject-name="' . e($this->subject_name) . '" title="Add New Item">
                            <i class="fa fa-plus"></i> Add
                        </a>
                        <a href="' . $viewUrl . '" target="_blank" rel="noopener" class="btn btn-sm btn-view" title="View Items">
                            <i class="fa fa-list"></i> View
                        </a>
                        <a href="' . $printUrl . '" target="_blank" class="btn btn-sm btn-print btn-icon" title="Print">
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

    protected function getPopupStyle()
    {
        return <<<CSS
        .scheme-popup .modal-dialog {
            width: calc(100vw - 32px);
            max-width: 1240px;
            margin: 16px auto;
        }
        .scheme-popup .modal-content {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 12px 32px rgba(19, 37, 58, .28);
            border: 1px solid #d8e3ee;
        }
        .scheme-popup .modal-header {
            background: linear-gradient(90deg, #f6fbff, #eef5fc);
            border-bottom: 1px solid #dbe7f2;
            position: sticky;
            top: 0;
            z-index: 3;
            padding: 12px 16px;
        }
        .scheme-popup .modal-title {
            font-size: 18px;
            font-weight: 700;
            color: #294055;
        }
        .scheme-popup .modal-body {
            max-height: calc(100vh - 170px);
            overflow-y: auto;
            background: #fcfeff;
            padding: 14px 16px 10px;
        }
        .scheme-popup .modal-footer {
            position: sticky;
            bottom: 0;
            z-index: 3;
            background: #fff;
            border-top: 1px solid #dbe7f2;
            padding: 10px 14px;
        }
        .scheme-popup .popup-feedback { display:none; margin-bottom: 10px; }
        .scheme-popup .form-grid { margin-bottom: 2px; }
        .scheme-popup .form-group { margin-bottom: 10px; }
        .scheme-popup label {
            font-size: 12px;
            margin-bottom: 4px;
            color: #3f4f5f;
            font-weight: 700;
        }
        .scheme-popup .form-control {
            height: 36px;
            border-radius: 8px;
            border-color: #d6e2ef;
        }
        .scheme-popup textarea.form-control {
            min-height: 88px;
            resize: vertical;
            line-height: 1.4;
        }
        .scheme-popup .section-title {
            font-weight: 700;
            color: #36485b;
            margin: 8px 0 10px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: .3px;
        }
        .scheme-popup .btn { border-radius: 8px; }
        .scheme-popup .btn + .btn { margin-left: 6px; }
        .scheme-popup .btn-save-more {
            background: #1f6f8b;
            border-color: #1f6f8b;
            color: #fff;
        }
        .scheme-popup .btn-save-more:hover {
            background: #1b6178;
            border-color: #1b6178;
            color: #fff;
        }
        .scheme-popup .btn[disabled] { opacity: .75; }
        /* Inline suggestion row directly below each field */
        .scheme-popup .inline-sugg-row {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            margin-top: 5px;
            margin-bottom: 4px;
        }
        .scheme-popup .sugg-chip {
            display: inline-block;
            border: 1px solid #c9dcef;
            background: #fff;
            color: #31506a;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            padding: 4px 9px;
            margin: 0 5px 5px 0;
            cursor: pointer;
            transition: all .15s ease-in-out;
        }
        .scheme-popup .sugg-chip:hover {
            border-color: #8bb7db;
            background: #eaf4ff;
            transform: translateY(-1px);
        }
        .scheme-popup .sugg-chip:active {
            transform: translateY(0);
        }
        .scheme-popup .field-append-flash {
            box-shadow: 0 0 0 2px rgba(79, 152, 220, .28);
        }

        @media (max-width: 991px) {
            .scheme-popup .modal-dialog {
                width: calc(100vw - 12px);
                margin: 6px auto;
            }
            .scheme-popup .modal-body {
                max-height: calc(100vh - 145px);
                padding: 12px 10px;
            }
            .scheme-popup .modal-title {
                font-size: 16px;
            }
            .scheme-popup .btn + .btn {
                margin-left: 4px;
            }
        }
        CSS;
    }

    public function storeItemAjax(Request $request)
    {
        $u = Admin::user();
        if (!$u) {
            return response()->json(['status' => false, 'message' => 'Unauthorized.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'subject_id' => 'required|integer',
            'term_id' => 'nullable|integer',
            'week' => 'required|integer|min:1|max:18',
            'period' => 'required|integer|min:1|max:10',
            'theme' => 'required|string|min:2|max:255',
            'topic' => 'required|string|min:2|max:255',
            'sub_topic' => 'required|string|min:2|max:255',
            'content' => 'required|string|min:3',
            'competence_subject' => 'required|string|min:3',
            'competence_language' => 'required|string|min:3',
            'methods' => 'required|string|min:3',
            'life_skills_values' => 'required|string|min:3',
            'suggested_activity' => 'required|string|min:3',
            'instructional_material' => 'required|string|min:2',
            'references' => 'required|string|min:2',
            'teacher_status' => 'nullable|in:Pending,Conducted,Skipped',
            'teacher_comment' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $subject = Subject::where('enterprise_id', $u->enterprise_id)
            ->where('id', (int) $request->subject_id)
            ->first();
        if (!$subject) {
            return response()->json(['status' => false, 'message' => 'Subject not found.'], 404);
        }

        $isPrivileged = $u->isRole('admin') || $u->isRole('dos') || $u->isRole('hm');
        if (!$isPrivileged) {
            $teacherIds = [(int) $subject->subject_teacher, (int) $subject->teacher_1, (int) $subject->teacher_2, (int) $subject->teacher_3];
            if (!in_array((int) $u->id, $teacherIds, true)) {
                return response()->json(['status' => false, 'message' => 'You are not allowed to add items for this subject.'], 403);
            }
        }

        $termId = (int) $request->term_id;
        if ($termId < 1) {
            $activeTerm = Term::where('enterprise_id', $u->enterprise_id)->where('is_active', 1)->first();
            if (!$activeTerm) {
                return response()->json(['status' => false, 'message' => 'No active term found.'], 422);
            }
            $termId = (int) $activeTerm->id;
        }

        $term = Term::where('enterprise_id', $u->enterprise_id)->where('id', $termId)->first();
        if (!$term) {
            return response()->json(['status' => false, 'message' => 'Invalid term selected.'], 422);
        }

        $item = new SchemWorkItem();
        $item->enterprise_id = $u->enterprise_id;
        $item->term_id = $termId;
        $item->subject_id = $subject->id;
        $item->teacher_id = (int) ($subject->subject_teacher ?: $u->id);
        $item->supervisor_id = (int) ($u->supervisor_id ?: $u->id);
        $item->week = (int) $request->week;
        $item->period = (int) $request->period;
        $item->theme = trim((string) $request->theme);
        $item->topic = trim((string) $request->topic);
        $item->sub_topic = trim((string) $request->sub_topic);
        $item->content = trim((string) $request->content);
        $item->competence_subject = trim((string) $request->competence_subject);
        $item->competence_language = trim((string) $request->competence_language);
        $item->methods = trim((string) $request->methods);
        $item->life_skills_values = trim((string) $request->life_skills_values);
        $item->suggested_activity = trim((string) $request->suggested_activity);
        $item->instructional_material = trim((string) $request->instructional_material);
        $item->references = trim((string) $request->references);
        $item->teacher_status = $request->teacher_status ?: 'Pending';
        $item->teacher_comment = trim((string) $request->teacher_comment);
        $item->supervisor_status = 'Pending';
        $item->status = 'Pending';

        // Legacy sync fields for backward compatibility in older views/reports.
        $item->supervisor_comment = $item->content;
        $item->competence = $item->competence_subject;
        $item->skills = $item->life_skills_values;

        $item->save();

        $total = SchemWorkItem::where(['subject_id' => $subject->id, 'term_id' => $termId])->count();
        $conducted = SchemWorkItem::where(['subject_id' => $subject->id, 'term_id' => $termId, 'teacher_status' => 'Conducted'])->count();
        $pending = SchemWorkItem::where(['subject_id' => $subject->id, 'term_id' => $termId, 'teacher_status' => 'Pending'])->count();
        $skipped = SchemWorkItem::where(['subject_id' => $subject->id, 'term_id' => $termId, 'teacher_status' => 'Skipped'])->count();
        $percent = $total > 0 ? round(($conducted / $total) * 100, 1) : 0;

        return response()->json([
            'status' => true,
            'message' => 'Scheme work item saved successfully.',
            'item_id' => $item->id,
            'stats' => [
                'total' => $total,
                'conducted' => $conducted,
                'pending' => $pending,
                'skipped' => $skipped,
                'percent' => $percent,
            ],
        ]);
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    /**
     * Override store to avoid calling form()->store() since this controller is read-only.
     */
    public function store()
    {
        admin_warning('Information', 'Subjects are managed in the Subjects section. This is a read-only view.');
        return redirect(admin_url('subjects'));
    }

    /**
     * Override update for the same reason.
     */
    public function update($id)
    {
        admin_warning('Information', 'Subjects are managed in the Subjects section. This is a read-only view.');
        return redirect(admin_url('subjects'));
    }

    protected function form()
    {
        // This controller is read-only. Subjects are managed in the Subjects section.
        // Redirect to the subjects management page.
        admin_warning('Information', 'Subjects are managed in the Subjects section. This is a read-only view for scheme work management.');
        return redirect(admin_url('subjects'));
    }
}
