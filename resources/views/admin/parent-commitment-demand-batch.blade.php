{{--
  Batch generator: turns parent commitment records into ONE school-fees demand,
  which then prints through the existing "Generate Demand Notices" flow.

  Each scope shows two counts: how many commitments match, and how many of those
  students still owe. Only the second number becomes notices — a parent who has
  since cleared is never sent a demand.
--}}
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-file-text-o"></i> Generate Fees Demands from Parent Commitments</h3>
        <div class="box-tools">
            <a href="{{ admin_url('parent-commitment-records') }}" class="btn btn-sm btn-default">
                <i class="fa fa-list"></i> Back to Commitments
            </a>
        </div>
    </div>

    <form method="POST" action="{{ $action }}">
        {!! csrf_field() !!}
        <div class="box-body">

            <div class="callout callout-info" style="margin-bottom:18px">
                <p style="margin:0">
                    The demand notice will quote each parent's own promise &mdash; who committed,
                    the amount, the date they committed to pay by, and how many days that date has
                    passed &mdash; alongside the balance outstanding today.
                </p>
            </div>

            <div class="form-group">
                <label>Which commitments?</label>
                @foreach ($scopes as $key => $label)
                    @php $c = $counts[$key] ?? ['commitments' => 0, 'owing' => 0]; @endphp
                    <div class="radio" style="margin:6px 0">
                        <label style="font-weight:normal">
                            <input type="radio" name="scope" value="{{ $key }}"
                                   {{ $key === 'overdue' ? 'checked' : '' }}>
                            <b>{{ $label }}</b>
                            <span class="text-muted" style="margin-left:8px">
                                {{ number_format($c['commitments']) }} commitment(s) &middot;
                                <span class="{{ $c['owing'] > 0 ? 'text-red' : 'text-muted' }}">
                                    <b>{{ number_format($c['owing']) }}</b> still owing
                                </span>
                            </span>
                        </label>
                    </div>
                @endforeach
                <small class="help-block" style="margin-top:2px">
                    Only students who <b>still owe</b> are included. Anyone who has cleared is skipped automatically.
                </small>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Committed to pay on or before</label>
                        <input type="date" name="due_by" class="form-control">
                        <small class="help-block">Applies to the "due on or before a date" option.</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Minimum committed balance (UGX)</label>
                        <input type="number" name="min_balance" class="form-control" min="0" step="1000"
                               placeholder="e.g. 100000">
                        <small class="help-block">Leave empty for no minimum.</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Demand title</label>
                        <input type="text" name="description" class="form-control" maxlength="255"
                               placeholder="Auto-generated if left empty">
                    </div>
                </div>
            </div>
        </div>

        <div class="box-footer">
            <button type="submit" class="btn btn-danger">
                <i class="fa fa-print"></i> Generate &amp; Print Demand Notices
            </button>
            <a href="{{ admin_url('school-fees-demands') }}" class="btn btn-default pull-right">
                <i class="fa fa-folder-open-o"></i> All Fees Demands
            </a>
        </div>
    </form>
</div>
