<?php

namespace App\Admin\Controllers;

use App\Models\Account;
use App\Models\AcademicClass;
use App\Models\ParentCommitmentRecord;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;

class ParentCommitmentRecordController extends AdminController
{
    protected $title = 'Parent Commitment Records';

    // =========================================================================
    // GRID — List view
    // =========================================================================

    protected function grid()
    {
        $u = Admin::user();

        // Auto-refresh overdue statuses whenever the bursar views the list
        ParentCommitmentRecord::markOverdue((int) $u->enterprise_id);

        $grid = new Grid(new ParentCommitmentRecord());

        $grid->model()
            ->where('enterprise_id', $u->enterprise_id)
            ->orderBy('commitment_date', 'asc')
            ->orderBy('id', 'desc');

        $grid->disableBatchActions();
        $grid->quickSearch('parent_name', 'parent_contact', 'comments')
            ->placeholder('Search by parent name, contact or notes...');

        $grid->tools(function ($tools) {
            $tools->append(
                '<a href="' . admin_url('parent-commitment-dashboard') . '" class="btn btn-sm btn-info">'
                . '<i class="fa fa-tachometer"></i> Commitment Dashboard</a>'
            );
        });

        // Filters
        $grid->filter(function ($filter) use ($u) {
            $filter->disableIdFilter();

            $filter->equal('promise_status', 'Status')->select([
                'Pending'   => 'Pending',
                'Fulfilled' => 'Fulfilled',
                'Overdue'   => 'Overdue',
            ]);

            $filter->between('commitment_date', 'Commitment Date')->date();

            $filter->like('parent_name', 'Parent Name');

            $ajax_url = url('/api/ajax-users?enterprise_id=' . $u->enterprise_id . '&search_by_1=name&search_by_2=id&user_type=student&model=User');
            $filter->equal('student_id', 'Student')
                ->select(function ($id) {
                    $s = User::find($id);
                    return $s ? [$s->id => '#' . $s->id . ' - ' . $s->name] : [];
                })
                ->ajax($ajax_url);
        });

        // Columns
        $grid->column('id', '#')->sortable()->width(50);

        $grid->column('student_id', 'Student')->display(function () {
            $name = optional($this->student)->name ?: '-';
            $class = '-';
            if ($this->student && $this->student->current_class_id) {
                $cls = AcademicClass::find($this->student->current_class_id);
                $class = $cls ? $cls->name_text : '-';
            }
            return $name . '<br><small class="text-muted">' . $class . '</small>';
        });

        $grid->column('parent_name', 'Parent')->display(function () {
            $name = $this->parent_name ?: '-';
            $contact = $this->parent_contact ?: '';
            return $name . ($contact ? '<br><small class="text-muted">' . $contact . '</small>' : '');
        });

        $grid->column('outstanding_balance', 'Outstanding (UGX)')->display(function ($v) {
            return '<strong>' . number_format((float) $v, 0) . '</strong>';
        })->sortable();

        $grid->column('commitment_date', 'Commits By')->display(function ($v) {
            if (!$v) return '-';
            $date = \Carbon\Carbon::parse($v);
            $isOverdue = $date->isPast() && $this->promise_status !== 'Fulfilled';
            $label = $date->format('d M Y');
            if ($isOverdue) {
                $label .= ' <span class="label label-danger" style="font-size:10px;">OVERDUE</span>';
            } elseif ($date->diffInDays(now(), false) > -8) {
                $label .= ' <span class="label label-warning" style="font-size:10px;">DUE SOON</span>';
            }
            return $label;
        })->sortable();

        $grid->column('promise_status', 'Status')->label([
            'Pending'   => 'warning',
            'Fulfilled' => 'success',
            'Overdue'   => 'danger',
        ])->sortable();

        $grid->column('comments', 'Comments')->limit(50)->display(function ($v) {
            return $v ?: '-';
        });

        $grid->column('created_at', 'Created')->display(function ($v) {
            return $v ? date('d M Y', strtotime($v)) : '-';
        })->sortable();

        return $grid;
    }

    // =========================================================================
    // DETAIL — Show individual record
    // =========================================================================

    protected function detail($id)
    {
        $show = new Show(ParentCommitmentRecord::findOrFail($id));

        $show->field('id', '#ID');
        $show->field('student_id', 'Student')->as(function () {
            return optional($this->student)->name ?: '-';
        });
        $show->field('parent_name', 'Parent Name');
        $show->field('parent_contact', 'Parent Contact');
        $show->field('outstanding_balance', 'Outstanding Balance (UGX)')->as(function ($v) {
            return number_format((float) $v, 0);
        });
        $show->field('commitment_date', 'Commitment Date');
        $show->field('promise_status', 'Status');
        $show->field('fulfilled_at', 'Fulfilled At');
        $show->field('comments', 'Comments');
        $show->field('created_at', 'Created At');
        $show->field('updated_at', 'Updated At');

        return $show;
    }

    // =========================================================================
    // FORM — Create / Edit
    // =========================================================================

    protected function form()
    {
        $form = new Form(new ParentCommitmentRecord());
        $u = Admin::user();

        $form->hidden('enterprise_id')->default($u->enterprise_id);
        $form->hidden('created_by')->default($u->id);
        $form->hidden('updated_by')->default($u->id);
        $form->hidden('parent_id');

        // ── Section 1: Student ───────────────────────────────────────────────
        $form->divider('Student Information');

        $ajax_url = url('/api/ajax-users?enterprise_id=' . $u->enterprise_id . '&search_by_1=name&search_by_2=id&user_type=student&model=User');
        $form->select('student_id', 'Student')
            ->options(function ($id) {
                $s = User::find($id);
                return $s ? [$s->id => '#' . $s->id . ' - ' . $s->name] : [];
            })
            ->ajax($ajax_url)
            ->rules('required')
            ->help('Search by student name or ID — parent details and outstanding balance will auto-fill below.');

        // ── Section 2: Parent Details ────────────────────────────────────────
        $form->divider('Parent Details');

        $form->text('parent_name', 'Parent / Guardian Name')
            ->placeholder('Auto-filled — edit if needed')
            ->rules('required|max:255');

        $form->text('parent_contact', 'Parent Contact / Phone')
            ->placeholder('Auto-filled — edit if needed')
            ->rules('nullable|max:100');

        // ── Section 3: Commitment Details ────────────────────────────────────
        $form->divider('Commitment Details');

        $form->currency('outstanding_balance', 'Outstanding Balance (UGX)')
            ->symbol('UGX')
            ->default(0)
            ->rules('required|numeric|min:0')
            ->help('Pre-filled from student ledger. Adjust to reflect the actual amount the parent is committing to pay.');

        $form->date('commitment_date', 'Commitment Date')
            ->rules('required')
            ->help('The date by which the parent has promised to pay the outstanding balance.');

        // ── Section 4: Status & Notes ────────────────────────────────────────
        $form->divider('Status & Notes');

        $form->select('promise_status', 'Promise Status')
            ->options([
                'Pending'   => 'Pending',
                'Fulfilled' => 'Fulfilled',
                'Overdue'   => 'Overdue',
            ])
            ->default('Pending')
            ->rules('required')
            ->help('Set to Fulfilled once the parent has paid. Overdue is set automatically.');

        $form->datetime('fulfilled_at', 'Fulfilled At')
            ->help('Auto-set when you mark the status as Fulfilled. You can also set/edit it manually.');

        $form->textarea('comments', 'Bursar Notes / Comments')
            ->rows(3)
            ->placeholder('Record any relevant notes about this commitment or follow-up actions taken.');

        // ── JavaScript: auto-fill + conditional fulfilled_at display ─────────
        $ajaxUrl = admin_url('parent-commitment-records/ajax/student-info');
        Admin::script(<<<JS
(function () {
    // Auto-fill parent details when a student is selected
    function fillStudentInfo(studentId) {
        if (!studentId) return;
        $.getJSON('{$ajaxUrl}', { q: studentId }, function (res) {
            if (!res || !res.success) return;
            $('input[name="parent_name"]').val(res.parent_name || '');
            $('input[name="parent_contact"]').val(res.parent_contact || '');
            $('input[name="outstanding_balance"]').val(res.outstanding_balance || 0);
            $('input[name="parent_id"]').val(res.parent_id || '');
        });
    }

    $(document).on('select2:select', 'select[name="student_id"]', function (e) {
        fillStudentInfo(e.params.data.id);
    });

    // Show / hide "Fulfilled At" based on status
    function toggleFulfilledAt() {
        var status = $('select[name="promise_status"]').val();
        var row = $('input[name="fulfilled_at"]').closest('.form-group');
        if (status === 'Fulfilled') {
            row.show();
        } else {
            row.hide();
        }
    }

    $(document).on('change', 'select[name="promise_status"]', toggleFulfilledAt);

    // Run on page load
    $(function () { toggleFulfilledAt(); });
}());
JS);

        // Saving callback: auto-set fulfilled_at, refresh updated_by
        $form->saving(function (Form $form) use ($u) {
            $form->updated_by = $u->id;

            if ($form->promise_status === 'Fulfilled') {
                // Auto-stamp only when not already provided
                if (empty($form->fulfilled_at)) {
                    $form->fulfilled_at = now();
                }
            } else {
                // Clear fulfilled_at only when status is being changed FROM Fulfilled
                if ($form->model()->promise_status === 'Fulfilled') {
                    $form->fulfilled_at = null;
                }
            }
        });

        $form->disableEditingCheck();
        $form->disableCreatingCheck();
        $form->disableViewCheck();

        return $form;
    }

    // =========================================================================
    // DASHBOARD — Bursar commitment overview
    // =========================================================================

    public function dashboard(Content $content)
    {
        $u = Admin::user();
        $eid = (int) $u->enterprise_id;

        // Refresh overdue statuses
        ParentCommitmentRecord::markOverdue($eid);

        $query = ParentCommitmentRecord::where('enterprise_id', $eid);

        $total     = (clone $query)->count();
        $pending   = (clone $query)->where('promise_status', 'Pending')->count();
        $fulfilled = (clone $query)->where('promise_status', 'Fulfilled')->count();
        $overdue   = (clone $query)->where('promise_status', 'Overdue')->count();

        $totalCommittedAmount = (clone $query)->sum('outstanding_balance');
        $pendingAmount        = (clone $query)->where('promise_status', 'Pending')->sum('outstanding_balance');
        $overdueAmount        = (clone $query)->where('promise_status', 'Overdue')->sum('outstanding_balance');
        $fulfilledAmount      = (clone $query)->where('promise_status', 'Fulfilled')->sum('outstanding_balance');

        $fulfillmentRate = $total > 0 ? round(($fulfilled / $total) * 100, 1) : 0.0;
        $overdueRate     = $total > 0 ? round(($overdue   / $total) * 100, 1) : 0.0;

        $statusBreakdown = [
            ['label' => 'Pending',   'count' => $pending,   'amount' => $pendingAmount,   'color' => '#f59e0b'],
            ['label' => 'Fulfilled', 'count' => $fulfilled, 'amount' => $fulfilledAmount, 'color' => '#10b981'],
            ['label' => 'Overdue',   'count' => $overdue,   'amount' => $overdueAmount,   'color' => '#ef4444'],
        ];

        // 7-day creation trend
        $start = now()->subDays(6)->startOfDay();
        $dailyRows = (clone $query)
            ->selectRaw('DATE(created_at) as day, count(*) as total, COALESCE(sum(outstanding_balance),0) as amount')
            ->whereDate('created_at', '>=', $start->toDateString())
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $dailyMap = [];
        foreach ($dailyRows as $r) {
            $dailyMap[(string) $r->day] = ['total' => (int) $r->total, 'amount' => (float) $r->amount];
        }

        $weeklyTrend = [];
        $weeklyMax   = 1;
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $key = $day->toDateString();
            $row = $dailyMap[$key] ?? ['total' => 0, 'amount' => 0];
            $weeklyTrend[] = [
                'date'   => $key,
                'label'  => $day->format('D'),
                'count'  => $row['total'],
                'amount' => $row['amount'],
            ];
            $weeklyMax = max($weeklyMax, $row['total']);
        }

        // Upcoming commitments: pending, due within 7 days
        $upcoming = (clone $query)
            ->where('promise_status', 'Pending')
            ->whereBetween('commitment_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->with(['student'])
            ->orderBy('commitment_date', 'asc')
            ->limit(20)
            ->get();

        // Overdue list (most recent first)
        $overdueList = (clone $query)
            ->where('promise_status', 'Overdue')
            ->with(['student'])
            ->orderBy('commitment_date', 'asc')
            ->limit(20)
            ->get();

        // Recent records
        $recent = (clone $query)
            ->with(['student'])
            ->orderBy('id', 'desc')
            ->limit(15)
            ->get();

        return $content
            ->title('Commitment Dashboard')
            ->description('Bursar overview of parent fee-payment commitments')
            ->body(view('admin.parent-commitment-dashboard', [
                'stats' => [
                    'total'              => $total,
                    'pending'            => $pending,
                    'fulfilled'          => $fulfilled,
                    'overdue'            => $overdue,
                    'total_amount'       => $totalCommittedAmount,
                    'pending_amount'     => $pendingAmount,
                    'overdue_amount'     => $overdueAmount,
                    'fulfilled_amount'   => $fulfilledAmount,
                    'fulfillment_rate'   => $fulfillmentRate,
                    'overdue_rate'       => $overdueRate,
                ],
                'statusBreakdown' => $statusBreakdown,
                'weeklyTrend'     => $weeklyTrend,
                'weeklyMax'       => $weeklyMax,
                'upcoming'        => $upcoming,
                'overdueList'     => $overdueList,
                'recent'          => $recent,
            ]));
    }

    // =========================================================================
    // AJAX — Return parent info and ledger balance for a selected student
    // =========================================================================

    public function ajaxStudentInfo(Request $request)
    {
        $u = Admin::user();
        $studentId = (int) $request->get('q');

        if ($studentId < 1) {
            return response()->json(['success' => false]);
        }

        $student = User::where([
            'enterprise_id' => $u->enterprise_id,
            'user_type'     => 'student',
        ])->find($studentId);

        if (!$student) {
            return response()->json(['success' => false]);
        }

        // Resolve parent
        $parent     = $student->parent_id ? User::find($student->parent_id) : null;
        $parentId   = $parent ? $parent->id : null;

        // Parent name: prefer linked parent record, then fall back to student's emergency/mother/father fields
        $parentName = '';
        if ($parent && !empty($parent->name)) {
            $parentName = $parent->name;
        } elseif (!empty($student->emergency_person_name)) {
            $parentName = $student->emergency_person_name;
        } elseif (!empty($student->mother_name)) {
            $parentName = $student->mother_name;
        } elseif (!empty($student->father_name)) {
            $parentName = $student->father_name;
        }

        // Parent contact
        $parentContact = '';
        if ($parent && !empty($parent->phone_number_1)) {
            $parentContact = $parent->phone_number_1;
        } elseif (!empty($student->phone_number_1)) {
            $parentContact = $student->phone_number_1;
        } elseif (!empty($student->emergency_person_phone)) {
            $parentContact = $student->emergency_person_phone;
        }

        // Outstanding balance from student ledger account
        $account            = Account::where([
            'administrator_id' => $student->id,
            'type'             => 'STUDENT_ACCOUNT',
        ])->first();
        $balance            = $account ? (float) $account->balance : 0.0;
        // Negative balance means student owes money; outstanding = abs of negative portion
        $outstandingBalance = $balance < 0 ? abs($balance) : 0.0;

        return response()->json([
            'success'             => true,
            'parent_id'           => $parentId,
            'parent_name'         => $parentName,
            'parent_contact'      => $parentContact,
            'outstanding_balance' => round($outstandingBalance, 2),
        ]);
    }
}
