{{-- One invoice: the document as the school sees it, with Newline's controls. --}}
@php
  $brand = config('newline.brand');
  $base = admin_url('subscriptions-admin');
  $statusClass = ['draft'=>'default','issued'=>'warning','paid'=>'success','void'=>'default'][$inv->status] ?? 'default';
@endphp
<style>
  .iv-head{background:#fff;border:1px solid #DCE3EA;padding:12px 14px;margin-bottom:12px}
  .iv-head .amt{font-size:26px;font-weight:800;color:{{ $brand }}}
  .iv-act form{display:inline}
  .iv-act .btn{margin:0 4px 6px 0}
  .iv-paper{background:#fff;border:1px solid #DCE3EA;padding:20px}
  .iv-link input{font-size:12px;padding:6px;border:1px solid #cfd8e0;border-radius:5px;width:100%;color:#405060}
</style>

<div class="iv-head">
  <div class="row">
    <div class="col-md-5">
      <div class="text-muted" style="font-size:11px;text-transform:uppercase;letter-spacing:.7px">{{ $inv->number }}</div>
      <div class="amt">UGX {{ number_format($inv->amount) }}</div>
      <div>{{ $inv->title }}</div>
      <div style="margin-top:6px">
        <span class="label label-{{ $statusClass }}">{{ strtoupper($inv->status) }}</span>
        @if($inv->isOverdue())<span class="label label-danger">OVERDUE</span>@endif
        @if($inv->sent_at)<span class="text-muted" style="font-size:12px">&nbsp;reminded {{ $inv->sent_at->diffForHumans() }}</span>@endif
      </div>
    </div>
    <div class="col-md-3">
      <div class="text-muted" style="font-size:11px;text-transform:uppercase">School</div>
      <div><a href="{{ $base }}/{{ $e->id }}"><b>{{ $e->name }}</b></a></div>
      <div class="text-muted" style="font-size:12px">Due {{ $inv->due_at ? $inv->due_at->format('d M Y') : '—' }}</div>
      <div class="text-muted" style="font-size:12px">Created {{ $inv->created_at->format('d M Y') }}</div>
    </div>
    <div class="col-md-4 iv-act text-right">
      <a class="btn btn-default btn-sm" href="{{ $base }}/invoices/{{ $inv->id }}/pdf" target="_blank" rel="noopener"><i class="fa fa-file-pdf-o"></i> PDF</a>
      @if($inv->isDraft())
        <form method="POST" action="{{ $base }}/invoices/{{ $inv->id }}/issue" onsubmit="return confirm('Issue {{ $inv->number }} to {{ addslashes($e->name) }}? They will see it on their dashboard immediately.')">
          {!! csrf_field() !!}<button class="btn btn-sm" style="background:{{ $brand }};color:#fff"><i class="fa fa-paper-plane"></i> Issue to school</button>
        </form>
      @endif
      @if($inv->isPayable())
        <form method="POST" action="{{ $base }}/invoices/{{ $inv->id }}/remind" onsubmit="return confirm('Send an SMS and email reminder to the school now?')">
          {!! csrf_field() !!}<button class="btn btn-warning btn-sm"><i class="fa fa-bell"></i> Send reminder</button>
        </form>
      @endif
      @if(!$inv->isPaid() && !$inv->isDraft())
        <form method="POST" action="{{ $base }}/invoices/{{ $inv->id }}/pay" class="form-inline" style="margin-top:6px">
          {!! csrf_field() !!}
          <select name="gateway" class="form-control input-sm"><option value="bank">bank</option><option value="cash">cash</option><option value="manual">manual</option></select>
          <input class="form-control input-sm" name="reference" placeholder="ref" style="width:90px">
          <button class="btn btn-success btn-sm" onclick="return confirm('Mark paid and activate access?')">Mark paid</button>
        </form>
      @endif
      @if(!$inv->isPaid())
        <form method="POST" action="{{ $base }}/invoices/{{ $inv->id }}/void" class="form-inline" style="margin-top:6px"
              onsubmit="return confirm('Void {{ $inv->number }}? The school stops seeing it.')">
          {!! csrf_field() !!}
          <input class="form-control input-sm" name="reason" placeholder="reason" style="width:120px">
          <button class="btn btn-default btn-sm">Void</button>
        </form>
      @endif
    </div>
  </div>
  @if(!$inv->isDraft())
  <div class="iv-link" style="margin-top:12px">
    <div class="text-muted" style="font-size:11px;text-transform:uppercase;letter-spacing:.7px">Shareable payment link (no login needed)</div>
    <div style="display:flex;gap:6px;margin-top:4px">
      <input readonly onclick="this.select()" value="{{ $inv->publicUrl() }}" style="flex:1;min-width:0">
      <a class="btn btn-default btn-sm" href="{{ $inv->publicUrl() }}" target="_blank" rel="noopener">Open</a>
    </div>
  </div>
  @endif
</div>

@if($payments->count())
<div class="iv-head">
  <b>Payments against this invoice</b>
  <table class="table table-condensed" style="margin:8px 0 0">
    <thead><tr><th>When</th><th>Gateway</th><th>Method</th><th>Reference</th><th class="text-right">Amount</th><th>Status</th></tr></thead>
    <tbody>
    @foreach($payments as $p)
      <tr>
        <td>{{ optional($p->received_at ?: $p->created_at)->format('d M Y H:i') }}</td>
        <td>{{ $p->gateway }}</td>
        <td>{{ $p->method ?: '—' }}</td>
        <td><small>{{ $p->confirmation_code ?: $p->merchant_ref }}</small></td>
        <td class="text-right">UGX {{ number_format($p->amount) }}</td>
        <td><span class="label label-{{ $p->status==='succeeded'?'success':($p->status==='pending'?'warning':'default') }}">{{ $p->status }}</span></td>
      </tr>
    @endforeach
    </tbody>
  </table>
</div>
@endif

<div class="iv-paper">
  @include('billing._invoice-css', ['co' => config('newline'), 'pdf' => false])
  @include('billing._invoice', \App\Services\InvoiceDocument::data($inv) + ['pdf' => false])
</div>
