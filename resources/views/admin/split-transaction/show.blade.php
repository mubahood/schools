<style>
.stv { font-family: inherit; }

/* header bar */
.stv-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 20px;
    background: linear-gradient(100deg,#1b4332,#2d6a4f);
    border-radius: 8px 8px 0 0;
    flex-wrap: wrap;
    gap: 10px;
}
.stv-title { color:#fff; font-size:1.05rem; font-weight:700; margin:0; display:flex; align-items:center; gap:8px; }
.stv-badge {
    display:inline-flex; align-items:center; gap:4px;
    padding:3px 10px; border-radius:20px; font-size:.72rem; font-weight:700;
}
.stv-badge-applied { background:rgba(40,167,69,.75); color:#fff; }
.stv-badge-draft   { background:rgba(253,126,20,.75);  color:#fff; }
.stv-back { font-size:.8rem; padding:5px 12px; border-radius:5px; background:rgba(255,255,255,.15); color:#fff; text-decoration:none; border:1px solid rgba(255,255,255,.25); }
.stv-back:hover { background:rgba(255,255,255,.25); color:#fff; }

/* content wrapper */
.stv-body { background:#fff; border:1px solid #d6dce4; border-top:none; border-radius:0 0 8px 8px; overflow:hidden; }

/* two-column overview */
.stv-overview { display:flex; border-bottom:1px solid #e3e8ee; }
.stv-col { flex:1; padding:16px 20px; border-right:1px solid #e3e8ee; min-width:0; }
.stv-col:last-child { border-right:none; }

.stv-sec-label {
    font-size:.65rem; font-weight:700; text-transform:uppercase; letter-spacing:.7px;
    color:#8a9ab0; margin-bottom:10px; display:flex; align-items:center; gap:5px;
}

/* key-value rows */
.stv-kv { display:grid; grid-template-columns:140px 1fr; gap:2px 0; font-size:.84rem; }
.stv-k { color:#8a9ab0; font-weight:600; padding:3px 0; line-height:1.5; }
.stv-v { color:#2c3e50; padding:3px 0; line-height:1.5; word-break:break-word; }
.stv-v a { color:#2d6a4f; font-weight:600; text-decoration:none; }
.stv-v a:hover { text-decoration:underline; }

/* amount stats */
.stv-amounts { display:flex; gap:8px; margin-top:8px; flex-wrap:wrap; }
.stv-amt {
    flex:1; min-width:100px;
    background:#f5f7fa; border-radius:7px; padding:8px 12px;
    border-left:3px solid #dee2e6;
}
.stv-amt-l { font-size:.63rem; font-weight:700; text-transform:uppercase; letter-spacing:.3px; color:#8a9ab0; margin-bottom:2px; }
.stv-amt-v { font-size:.95rem; font-weight:700; font-variant-numeric:tabular-nums; color:#2c3e50; }
.stv-amt--orig     { border-left-color:#6c757d; }
.stv-amt--remaining { border-left-color:#28a745; }
.stv-amt--split    { border-left-color:#e63946; }

/* distribution table */
.stv-table-wrap { padding:0; }
.stv-dist-head {
    padding:10px 20px 8px;
    font-size:.65rem; font-weight:700; text-transform:uppercase; letter-spacing:.7px;
    color:#8a9ab0; border-top:1px solid #e3e8ee; background:#fafbfc;
    display:flex; align-items:center; gap:5px;
}
.stv-table { width:100%; border-collapse:collapse; font-size:.84rem; }
.stv-table thead th {
    background:#f5f7fa; color:#6c757d; font-size:.72rem; font-weight:700;
    text-transform:uppercase; letter-spacing:.4px;
    padding:8px 14px; border-bottom:2px solid #e3e8ee; white-space:nowrap;
}
.stv-table tbody td { padding:9px 14px; border-bottom:1px solid #f0f2f5; vertical-align:middle; }
.stv-table tbody tr:last-child td { border-bottom:none; }
.stv-table tfoot td {
    padding:9px 14px; font-weight:700; font-size:.85rem;
    border-top:2px solid #e3e8ee; background:#fafbfc;
}
.stv-table tbody tr:hover td { background:#f9fbfc; }

.stv-txn-link {
    display:inline-flex; align-items:center; gap:4px;
    padding:3px 9px; border-radius:5px;
    background:#e8f5e9; color:#2d6a4f; font-weight:600; font-size:.8rem;
    text-decoration:none; border:1px solid #c8e6c9;
}
.stv-txn-link:hover { background:#c8e6c9; color:#1b4332; }

.stv-stu-link {
    display:inline-flex; align-items:center; gap:4px;
    color:#1a56db; font-weight:600; text-decoration:none;
}
.stv-stu-link:hover { text-decoration:underline; }

.stv-notes {
    margin:0; padding:10px 20px;
    background:#fffbf0; border-top:1px solid #ffe8a1;
    font-size:.83rem; color:#7a5c00; display:flex; gap:8px; align-items:flex-start;
}
.stv-notes i { margin-top:1px; flex-shrink:0; }

@media(max-width:767px) {
    .stv-overview { flex-direction:column; }
    .stv-col { border-right:none; border-bottom:1px solid #e3e8ee; }
}
</style>

@php
    $origTxn     = $split->originalTransaction;
    $origStudent = optional(optional($origTxn)->account)->owner;
    $totalSplit  = $split->items->sum('amount');
@endphp

<div class="stv">

    {{-- Header ──────────────────────────────────────────────── --}}
    <div class="stv-head">
        <div class="stv-title">
            <i class="fa fa-code-fork"></i>
            Split Transaction #{{ $split->id }}
            <span class="stv-badge {{ $split->status === 'Applied' ? 'stv-badge-applied' : 'stv-badge-draft' }}">
                <i class="fa fa-{{ $split->status === 'Applied' ? 'check' : 'clock-o' }}"></i>
                {{ $split->status }}
            </span>
        </div>
        <a href="{{ url('split-transactions') }}" class="stv-back">
            <i class="fa fa-arrow-left"></i> Back to list
        </a>
    </div>

    <div class="stv-body">

        {{-- Two-column overview ─────────────────────────────── --}}
        <div class="stv-overview">

            {{-- Left: original transaction details --}}
            <div class="stv-col">
                <div class="stv-sec-label"><i class="fa fa-file-text-o"></i> Original Transaction</div>
                <div class="stv-kv">
                    <span class="stv-k">Transaction</span>
                    <span class="stv-v">
                        <a href="{{ url('transactions/' . $split->original_transaction_id) }}" target="_blank">
                            <i class="fa fa-external-link" style="font-size:.7rem"></i> Txn #{{ $split->original_transaction_id }}
                        </a>
                    </span>

                    <span class="stv-k">Student</span>
                    <span class="stv-v">
                        @if($origStudent)
                            <a href="{{ url('students/' . $origStudent->id) }}" class="stv-stu-link">
                                <i class="fa fa-user-o" style="font-size:.72rem"></i>
                                {{ $origStudent->name }}
                            </a>
                        @else
                            —
                        @endif
                    </span>

                    <span class="stv-k">Description</span>
                    <span class="stv-v" style="font-size:.78rem; color:#555;">
                        {{ optional($origTxn)->description ?? '—' }}
                    </span>

                    <span class="stv-k">Split Date</span>
                    <span class="stv-v">{{ $split->created_at->format('d M Y — H:i') }}</span>

                    <span class="stv-k">Applied By</span>
                    <span class="stv-v">
                        @if($split->createdBy)
                            <i class="fa fa-user-circle-o" style="color:#8a9ab0; font-size:.78rem"></i>
                            {{ $split->createdBy->name }}
                        @else —
                        @endif
                    </span>
                </div>
            </div>

            {{-- Right: financial summary --}}
            <div class="stv-col" style="flex:.9">
                <div class="stv-sec-label"><i class="fa fa-bar-chart"></i> Financial Summary</div>
                <div class="stv-amounts">
                    <div class="stv-amt stv-amt--orig">
                        <div class="stv-amt-l">Original Amount</div>
                        <div class="stv-amt-v" style="color:#6c757d">{{ number_format($split->original_amount) }}</div>
                    </div>
                    <div class="stv-amt stv-amt--remaining">
                        <div class="stv-amt-l">Kept by Original</div>
                        <div class="stv-amt-v" style="color:#28a745">{{ number_format($split->original_remaining_amount) }}</div>
                    </div>
                    <div class="stv-amt stv-amt--split">
                        <div class="stv-amt-l">Distributed</div>
                        <div class="stv-amt-v" style="color:#e63946">{{ number_format($totalSplit) }}</div>
                    </div>
                </div>

                {{-- Balance check --}}
                @php $balanced = ($split->original_remaining_amount + $totalSplit) === (int)$split->original_amount; @endphp
                <div style="margin-top:10px; font-size:.78rem; padding:6px 10px; border-radius:5px;
                    background: {{ $balanced ? '#e8f5e9' : '#fff3cd' }};
                    color: {{ $balanced ? '#1b5e20' : '#856404' }};
                    border: 1px solid {{ $balanced ? '#c8e6c9' : '#ffc107' }}">
                    <i class="fa fa-{{ $balanced ? 'check-circle' : 'exclamation-triangle' }}"></i>
                    {{ $balanced
                        ? number_format($split->original_remaining_amount) . ' + ' . number_format($totalSplit) . ' = ' . number_format($split->original_amount) . ' ✓ Balanced'
                        : 'Amounts do not balance — check records'
                    }}
                </div>
            </div>

        </div>

        {{-- Notes bar ───────────────────────────────────────── --}}
        @if($split->notes)
        <div class="stv-notes">
            <i class="fa fa-sticky-note-o"></i>
            <span><strong>Notes:</strong> {{ $split->notes }}</span>
        </div>
        @endif

        {{-- Distribution table ──────────────────────────────── --}}
        <div class="stv-dist-head">
            <i class="fa fa-share-alt"></i> Distributed To — {{ $split->items->count() }} student(s)
        </div>
        <div style="overflow-x:auto">
        <table class="stv-table">
            <thead>
                <tr>
                    <th style="width:40px">#</th>
                    <th>Student</th>
                    <th>Amount (UGX)</th>
                    <th>New Transaction</th>
                    <th>Student Profile</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($split->items as $i => $item)
                @php
                    $student = optional(optional($item->toAccount)->owner);
                @endphp
                <tr>
                    <td style="color:#8a9ab0; font-weight:600">{{ $i + 1 }}</td>
                    <td>
                        @if($student->name)
                            <i class="fa fa-user-o" style="color:#8a9ab0; font-size:.8rem"></i>
                            <strong>{{ $student->name }}</strong>
                        @else
                            <span class="text-muted">Account #{{ $item->to_account_id }}</span>
                        @endif
                    </td>
                    <td>
                        <strong style="font-variant-numeric:tabular-nums; font-size:.9rem">
                            {{ number_format($item->amount) }}
                        </strong>
                    </td>
                    <td>
                        @if($item->to_transaction_id)
                            <a href="{{ url('transactions/' . $item->to_transaction_id) }}" target="_blank" class="stv-txn-link">
                                <i class="fa fa-external-link" style="font-size:.65rem"></i>
                                Txn #{{ $item->to_transaction_id }}
                            </a>
                        @else
                            <span style="color:#ccc">—</span>
                        @endif
                    </td>
                    <td>
                        @if($student->id)
                            <a href="{{ url('students/' . $student->id) }}" class="stv-stu-link" title="View {{ $student->name }}'s profile">
                                <i class="fa fa-user-circle-o" style="font-size:.8rem"></i> View Profile
                            </a>
                        @else
                            <span style="color:#ccc">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="text-align:right; color:#8a9ab0">Total Distributed</td>
                    <td style="color:#e63946; font-variant-numeric:tabular-nums">{{ number_format($totalSplit) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
        </div>

    </div>
</div>
