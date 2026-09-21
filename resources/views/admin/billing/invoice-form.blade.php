{{-- Newline's invoice builder. --}}
@php $brand = config('newline.brand'); @endphp
<style>
  .if-box{background:#fff;border:1px solid #e0e6ec;border-radius:10px;padding:18px 20px;margin-bottom:16px}
  .if-box h4{margin:0 0 12px;font-size:14px;text-transform:uppercase;letter-spacing:.7px;color:{{ $brand }}}
  .if-items th{font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:#6B7A8C;font-weight:600}
  .if-items td{padding:5px 4px;vertical-align:top}
  .if-items input{font-size:13px}
  .if-total{font-size:22px;font-weight:800;color:{{ $brand }}}
  .if-hint{font-size:12px;color:#6B7A8C}
  .if-chip{display:inline-block;background:#EAF7FD;color:#00688F;border:1px solid #BEE6F7;border-radius:20px;padding:3px 11px;font-size:12px;margin:0 5px 5px 0;cursor:pointer}
</style>

<form method="POST" action="{{ admin_url('subscriptions-admin/'.$e->id.'/invoices') }}" id="invForm">
  {!! csrf_field() !!}

  <div class="if-box">
    <h4>What is being billed</h4>
    <div class="row">
      <div class="col-md-6">
        <label>Invoice title *</label>
        <input class="form-control" name="title" required maxlength="190"
               value="{{ old('title', 'School Management System — Licence & Support') }}">
      </div>
      <div class="col-md-6">
        <label>Sub-heading shown under the amount</label>
        <input class="form-control" name="description" maxlength="255"
               value="{{ old('description') }}" placeholder="e.g. Custom package licence for Term 3, 2026">
      </div>
    </div>
    <div class="row" style="margin-top:12px">
      <div class="col-md-6">
        <label>Term activated when this invoice is paid</label>
        <select class="form-control" name="term_id">
          <option value="">— none (licence only, no term activation) —</option>
          @foreach($terms as $t)
            <option value="{{ $t->id }}" {{ old('term_id', optional($suggestedTerm)->id) == $t->id ? 'selected' : '' }}>
              {{ is_numeric(trim($t->name)) ? 'Term '.trim($t->name) : trim($t->name) }}{{ $t->year_name ? ', '.trim($t->year_name) : '' }}
              ({{ $t->starts }} → {{ $t->ends }}){{ $t->is_active ? ' — currently active' : '' }}
            </option>
          @endforeach
        </select>
        <div class="if-hint">On payment this term is set active, its year with it, and access runs to the term's end plus the grace window.</div>
      </div>
      <div class="col-md-6">
        <label>Payment deadline *</label>
        <input class="form-control" type="date" name="due_at" required value="{{ old('due_at', $defaultDue) }}">
        <div class="if-hint">Shown to the school on their dashboard and counted down daily.</div>
      </div>
    </div>
  </div>

  <div class="if-box">
    <h4>Line items</h4>
    <div class="if-hint" style="margin-bottom:8px">
      This school has <b>{{ number_format($students) }}</b> active students.
      <span class="if-chip" onclick="fillPerStudent(3000)">Per-student licence @ 3,000</span>
      <span class="if-chip" onclick="fillPerStudent(2500)">@ 2,500</span>
      <span class="if-chip" onclick="fillPerStudent(2000)">@ 2,000</span>
    </div>
    <table class="table if-items" id="itemsTable">
      <thead>
        <tr><th style="width:34%">Description *</th><th style="width:26%">Detail line</th><th style="width:11%">Qty</th>
            <th style="width:10%">Unit</th><th style="width:13%">Rate (UGX)</th><th style="width:6%"></th></tr>
      </thead>
      <tbody></tbody>
    </table>
    <button type="button" class="btn btn-default btn-sm" onclick="addRow()">+ Add line</button>
    <div class="pull-right" style="text-align:right">
      <div class="if-hint">Invoice total</div>
      <div class="if-total" id="grand">UGX 0</div>
    </div>
    <div style="clear:both"></div>
  </div>

  <div class="if-box">
    <h4>What the licence covers</h4>
    <textarea class="form-control" name="inclusions" rows="6" placeholder="One feature per line">{{ old('inclusions') }}</textarea>
    <div class="if-hint">One per line. These print as a ticked list on the invoice — this is where a custom package is justified.</div>
  </div>

  <div class="if-box">
    <h4>Notes on the invoice</h4>
    <textarea class="form-control" name="notes" rows="5" placeholder="Terms, what the rate reflects, anything the finance committee should read.">{{ old('notes') }}</textarea>
  </div>

  <div class="if-box">
    <label style="font-weight:400">
      <input type="checkbox" name="issue_now" value="1"> Issue to the school immediately
    </label>
    <div class="if-hint">Leave unticked to save a draft. A draft is invisible to the school until you issue it — read the PDF first.</div>
    <div style="margin-top:12px">
      <button class="btn btn-primary" style="background:{{ $brand }};border-color:{{ $brand }}">Create invoice</button>
      <a class="btn btn-default" href="{{ admin_url('subscriptions-admin/'.$e->id) }}">Cancel</a>
    </div>
  </div>
</form>

<script>
  var ROW = 0;
  function addRow(v) {
    v = v || {};
    var i = ROW++;
    var tr = document.createElement('tr');
    tr.innerHTML =
      '<td><input class="form-control" name="items[' + i + '][label]" value="' + (v.label || '') + '" placeholder="e.g. System licence — per active student"></td>' +
      '<td><input class="form-control" name="items[' + i + '][description]" value="' + (v.description || '') + '" placeholder="optional detail"></td>' +
      '<td><input class="form-control it-qty" type="number" min="1" name="items[' + i + '][quantity]" value="' + (v.quantity || 1) + '"></td>' +
      '<td><input class="form-control" name="items[' + i + '][unit]" value="' + (v.unit || '') + '" placeholder="students"></td>' +
      '<td><input class="form-control it-rate" type="number" min="0" name="items[' + i + '][unit_amount]" value="' + (v.unit_amount || 0) + '"></td>' +
      '<td class="text-right"><button type="button" class="btn btn-xs btn-default" onclick="this.closest(\'tr\').remove();total()">✕</button></td>';
    document.querySelector('#itemsTable tbody').appendChild(tr);
    tr.querySelectorAll('input').forEach(function (el) { el.addEventListener('input', total); });
    total();
  }
  function total() {
    var sum = 0;
    document.querySelectorAll('#itemsTable tbody tr').forEach(function (tr) {
      sum += (parseInt(tr.querySelector('.it-qty').value, 10) || 0) * (parseInt(tr.querySelector('.it-rate').value, 10) || 0);
    });
    document.getElementById('grand').textContent = 'UGX ' + sum.toLocaleString();
  }
  function fillPerStudent(rate) {
    document.querySelector('#itemsTable tbody').innerHTML = '';
    addRow({
      label: 'School Management System — licence & support (custom package)',
      description: 'Per active student, for the term billed',
      quantity: {{ (int) $students }}, unit: 'students', unit_amount: rate
    });
  }
  addRow();
</script>
