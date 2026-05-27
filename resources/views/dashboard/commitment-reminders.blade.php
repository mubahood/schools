@php
    use App\Models\ParentCommitmentRecord;
    $eid = \Encore\Admin\Facades\Admin::user()->enterprise_id;

    // Auto-refresh overdue statuses
    ParentCommitmentRecord::markOverdue((int) $eid);

    $overdue  = ParentCommitmentRecord::where('enterprise_id', $eid)->where('promise_status','Overdue')->count();
    $upcoming = ParentCommitmentRecord::where('enterprise_id', $eid)->where('promise_status','Pending')
        ->whereBetween('commitment_date',[now()->toDateString(), now()->addDays(7)->toDateString()])
        ->count();
    $pending  = ParentCommitmentRecord::where('enterprise_id', $eid)->where('promise_status','Pending')->count();

    $overdueAmt  = (float) ParentCommitmentRecord::where('enterprise_id',$eid)->where('promise_status','Overdue')->sum('outstanding_balance');
    $upcomingAmt = (float) ParentCommitmentRecord::where('enterprise_id',$eid)->where('promise_status','Pending')
        ->whereBetween('commitment_date',[now()->toDateString(), now()->addDays(7)->toDateString()])
        ->sum('outstanding_balance');

    $upcomingList = ParentCommitmentRecord::where('enterprise_id',$eid)->where('promise_status','Pending')
        ->whereBetween('commitment_date',[now()->toDateString(), now()->addDays(7)->toDateString()])
        ->with('student')->orderBy('commitment_date')->limit(5)->get();

    $overdueList = ParentCommitmentRecord::where('enterprise_id',$eid)->where('promise_status','Overdue')
        ->with('student')->orderBy('commitment_date')->limit(5)->get();
@endphp

@if($overdue > 0 || $upcoming > 0 || $pending > 0)
<div style="background:#fff;border:1px solid #dde3ec;margin-bottom:12px;">
    {{-- Header bar --}}
    <div style="background:#1c3a5e;color:#fff;padding:8px 14px;display:flex;justify-content:space-between;align-items:center;">
        <span style="font-weight:700;font-size:13px;">
            <i class="fa fa-handshake-o"></i>&nbsp; Parent Fee Commitments
        </span>
        <a href="{{ admin_url('parent-commitment-dashboard') }}" style="color:#93c5fd;font-size:11px;">
            View Dashboard →
        </a>
    </div>

    <div style="padding:10px 14px;">
        {{-- Summary badges --}}
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px;">
            @if($overdue > 0)
            <a href="{{ admin_url('parent-commitment-records?promise_status=Overdue') }}"
               style="display:flex;align-items:center;gap:6px;background:#fef2f2;border:1px solid #fecaca;padding:5px 10px;border-radius:3px;text-decoration:none;">
                <span style="background:#ef4444;color:#fff;border-radius:50%;width:22px;height:22px;display:inline-flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;">{{ $overdue }}</span>
                <span style="font-size:12px;color:#7f1d1d;font-weight:600;">OVERDUE</span>
                <span style="font-size:11px;color:#9ca3af;">UGX {{ number_format($overdueAmt, 0) }}</span>
            </a>
            @endif

            @if($upcoming > 0)
            <a href="{{ admin_url('parent-commitment-dashboard') }}"
               style="display:flex;align-items:center;gap:6px;background:#fffbeb;border:1px solid #fde68a;padding:5px 10px;border-radius:3px;text-decoration:none;">
                <span style="background:#f59e0b;color:#fff;border-radius:50%;width:22px;height:22px;display:inline-flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;">{{ $upcoming }}</span>
                <span style="font-size:12px;color:#92400e;font-weight:600;">DUE IN 7 DAYS</span>
                <span style="font-size:11px;color:#9ca3af;">UGX {{ number_format($upcomingAmt, 0) }}</span>
            </a>
            @endif

            @if($pending > 0)
            <a href="{{ admin_url('parent-commitment-records?promise_status=Pending') }}"
               style="display:flex;align-items:center;gap:6px;background:#f0f9ff;border:1px solid #bae6fd;padding:5px 10px;border-radius:3px;text-decoration:none;">
                <span style="font-size:12px;color:#075985;font-weight:600;">{{ $pending }} Pending total</span>
            </a>
            @endif

            <a href="{{ admin_url('parent-commitment-records/create') }}"
               style="margin-left:auto;background:#1c3a5e;color:#fff;padding:5px 12px;border-radius:3px;font-size:11px;text-decoration:none;align-self:center;">
                <i class="fa fa-plus"></i> Record Commitment
            </a>
        </div>

        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            {{-- Overdue mini-list --}}
            @if($overdueList->count() > 0)
            <div style="flex:1;min-width:260px;">
                <div style="font-size:11px;font-weight:700;color:#ef4444;text-transform:uppercase;letter-spacing:.3px;margin-bottom:5px;">
                    <i class="fa fa-exclamation-circle"></i> Overdue Commitments
                </div>
                <table style="width:100%;border-collapse:collapse;font-size:11px;">
                    @foreach($overdueList as $r)
                    @php $daysLate = $r->commitment_date ? (int)\Carbon\Carbon::parse($r->commitment_date)->diffInDays(now()) : 0; @endphp
                    <tr style="border-bottom:1px solid #f3f4f6;">
                        <td style="padding:3px 4px 3px 0;">
                            <strong>{{ optional($r->student)->name ?? $r->parent_name }}</strong><br>
                            <span style="color:#6b7280;">{{ $r->parent_contact }}</span>
                        </td>
                        <td style="padding:3px 4px;text-align:right;color:#ef4444;font-weight:600;">
                            {{ number_format((float)$r->outstanding_balance, 0) }}<br>
                            <span style="color:#9ca3af;font-weight:400;">{{ $daysLate }}d late</span>
                        </td>
                        <td style="padding:3px 0 3px 4px;text-align:right;">
                            <a href="{{ admin_url('parent-commitment-records/'.$r->id.'/edit') }}"
                               style="font-size:10px;background:#ef4444;color:#fff;padding:2px 6px;border-radius:2px;text-decoration:none;">Fulfil</a>
                        </td>
                    </tr>
                    @endforeach
                </table>
                @if($overdue > 5)
                <a href="{{ admin_url('parent-commitment-records?promise_status=Overdue') }}" style="font-size:10px;color:#ef4444;">+ {{ $overdue - 5 }} more overdue →</a>
                @endif
            </div>
            @endif

            {{-- Upcoming mini-list --}}
            @if($upcomingList->count() > 0)
            <div style="flex:1;min-width:260px;">
                <div style="font-size:11px;font-weight:700;color:#d97706;text-transform:uppercase;letter-spacing:.3px;margin-bottom:5px;">
                    <i class="fa fa-clock-o"></i> Due This Week
                </div>
                <table style="width:100%;border-collapse:collapse;font-size:11px;">
                    @foreach($upcomingList as $r)
                    @php $daysLeft = $r->commitment_date ? max(0,(int)\Carbon\Carbon::parse($r->commitment_date)->diffInDays(now(),false)*-1) : 0; @endphp
                    <tr style="border-bottom:1px solid #f3f4f6;">
                        <td style="padding:3px 4px 3px 0;">
                            <strong>{{ optional($r->student)->name ?? $r->parent_name }}</strong><br>
                            <span style="color:#6b7280;">Due {{ $r->commitment_date ? \Carbon\Carbon::parse($r->commitment_date)->format('d M') : '-' }}</span>
                        </td>
                        <td style="padding:3px 4px;text-align:right;color:#d97706;font-weight:600;">
                            {{ number_format((float)$r->outstanding_balance, 0) }}<br>
                            <span style="color:#9ca3af;font-weight:400;">{{ $daysLeft }}d left</span>
                        </td>
                        <td style="padding:3px 0 3px 4px;text-align:right;">
                            <a href="{{ admin_url('parent-commitment-records/'.$r->id.'/edit') }}"
                               style="font-size:10px;background:#f59e0b;color:#fff;padding:2px 6px;border-radius:2px;text-decoration:none;">Edit</a>
                        </td>
                    </tr>
                    @endforeach
                </table>
                @if($upcoming > 5)
                <a href="{{ admin_url('parent-commitment-dashboard') }}" style="font-size:10px;color:#d97706;">+ {{ $upcoming - 5 }} more due soon →</a>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endif
