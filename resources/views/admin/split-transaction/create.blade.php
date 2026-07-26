<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-code-fork"></i> Split a Transaction</h3>
        <div class="box-tools">
            <a href="{{ url('split-transactions') }}" class="btn btn-sm btn-default"><i class="fa fa-arrow-left"></i> Back to list</a>
        </div>
    </div>

    <div class="box-body">

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ url('split-transactions') }}" id="split-form">
            @csrf

            {{-- ── Step 1: Pick original transaction ──────────────────────── --}}
            <div class="panel panel-default">
                <div class="panel-heading"><b>Step 1 — Find the Original Transaction</b></div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group">
                                <label>Search by student name, description, or transaction ID</label>
                                <select id="txn-picker" style="width:100%">
                                    <option value="">— type to search —</option>
                                    @foreach ($transactions as $t)
                                    <option value="{{ $t->id }}"
                                        data-amount="{{ (int)$t->amount }}"
                                        data-student="{{ e($t->student_name) }}"
                                        data-date="{{ $t->payment_date ?? '' }}"
                                        data-desc="{{ e($t->description ?? '') }}"
                                        data-source="{{ $t->source ?? '' }}"
                                        {{ $prefillTxn && $prefillTxn->id == $t->id ? 'selected' : '' }}>
                                        #{{ $t->id }} — {{ $t->student_name }} — UGX {{ number_format((int)$t->amount) }}{{ $t->payment_date ? ' (' . date('d M Y', strtotime($t->payment_date)) . ')' : '' }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="txn-detail" style="display:none" class="alert alert-info mt-2">
                        <input type="hidden" name="original_transaction_id" id="txn-id-field">
                        <table class="table table-condensed table-bordered mb-0" style="background:#fff">
                            <tr><th style="width:160px">Transaction #</th><td id="d-id"></td></tr>
                            <tr><th>Student</th><td id="d-student"></td></tr>
                            <tr><th>Original Amount</th><td><strong id="d-amount" class="text-success"></strong></td></tr>
                            <tr><th>Date</th><td id="d-date"></td></tr>
                            <tr><th>Description</th><td id="d-desc"></td></tr>
                            <tr><th>Source</th><td id="d-source"></td></tr>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ── Step 2: Define the split ────────────────────────────────── --}}
            <div id="split-section" style="display:none">
                <div class="panel panel-default">
                    <div class="panel-heading"><b>Step 2 — Define the Split</b></div>
                    <div class="panel-body">

                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>Amount Remaining on Original Student <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon">UGX</span>
                                        <input type="number" name="original_remaining_amount" id="remaining-amount"
                                               class="form-control" min="0" step="1" placeholder="0"
                                               value="{{ old('original_remaining_amount', 0) }}"
                                               oninput="updateBalance()">
                                    </div>
                                    <span class="help-block">How much stays on the original student's account.</span>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <h4>Split Portions <small>— allocate to other students</small></h4>

                        <table class="table table-bordered table-condensed" id="items-table">
                            <thead>
                                <tr>
                                    <th>Student Account (receiving portion)</th>
                                    <th style="width:210px">Amount (UGX)</th>
                                    <th style="width:46px"></th>
                                </tr>
                            </thead>
                            <tbody id="items-body"></tbody>
                        </table>

                        <button type="button" class="btn btn-sm btn-default" onclick="addItem()">
                            <i class="fa fa-plus"></i> Add Student
                        </button>

                        <div class="well well-sm mt-3" style="max-width:380px">
                            <table style="width:100%;font-size:14px">
                                <tr><td>Original Amount</td>
                                    <td style="text-align:right"><strong id="bc-original">—</strong></td></tr>
                                <tr><td>Remaining on original</td>
                                    <td style="text-align:right" id="bc-remaining">—</td></tr>
                                <tr><td>Total to other students</td>
                                    <td style="text-align:right" id="bc-split">—</td></tr>
                                <tr style="border-top:2px solid #ccc">
                                    <td><strong>Balance</strong></td>
                                    <td style="text-align:right"><strong id="bc-status">—</strong></td></tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading"><b>Notes (optional)</b></div>
                    <div class="panel-body">
                        <textarea name="notes" class="form-control" rows="2"
                                  placeholder="Reason for split…">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div>
                    <button type="submit" class="btn btn-primary btn-lg" id="submit-btn" disabled>
                        <i class="fa fa-check"></i> Apply Split
                    </button>
                    <span class="text-muted" style="margin-left:10px">Irreversible once applied.</span>
                </div>
            </div>

        </form>
    </div>
</div>

<script>
var originalAmount = 0;
// Same AJAX URL pattern as TransactionController
var STUDENT_AJAX_URL = '{{ url("/api/studentsFinancialAccounts?user_id=" . $u->id) }}';

// ── Transaction picker ──────────────────────────────────────────────────────
$(function() {
    $('#txn-picker').select2({
        placeholder: 'Type to search…',
        allowClear: true,
        width: '100%'
    }).on('change', function() {
        var opt = $(this).find(':selected');
        var id  = parseInt(opt.val()) || 0;
        if (!id) {
            document.getElementById('txn-detail').style.display    = 'none';
            document.getElementById('split-section').style.display = 'none';
            originalAmount = 0;
            return;
        }
        fillTransaction({
            id:          id,
            amount:      parseInt(opt.data('amount')) || 0,
            student:     opt.data('student')   || '',
            date:        opt.data('date')       || '',
            description: opt.data('desc')       || '',
            source:      opt.data('source')     || '',
        });
    });

    @if ($prefillTxn)
        $('#txn-picker').trigger('change');
    @endif
});

function fillTransaction(t) {
    document.getElementById('txn-id-field').value     = t.id;
    document.getElementById('d-id').textContent       = '#' + t.id;
    document.getElementById('d-student').textContent  = t.student;
    document.getElementById('d-amount').textContent   = 'UGX ' + fmt(t.amount);
    document.getElementById('d-date').textContent     = t.date        || '—';
    document.getElementById('d-desc').textContent     = t.description || '—';
    document.getElementById('d-source').textContent   = t.source      || '—';

    originalAmount = t.amount;
    document.getElementById('bc-original').textContent = 'UGX ' + fmt(originalAmount);
    document.getElementById('txn-detail').style.display    = 'block';
    document.getElementById('split-section').style.display = 'block';

    document.getElementById('remaining-amount').value = t.amount;
    updateBalance();
    if (document.getElementById('items-body').rows.length === 0) addItem();
}

// ── Items table with AJAX student search (same API as TransactionController) ─
var itemIndex = 0;

function addItem() {
    var idx   = itemIndex++;
    var tbody = document.getElementById('items-body');
    var tr    = document.createElement('tr');
    tr.id     = 'item-row-' + idx;
    tr.innerHTML =
        '<td>' +
        '  <select name="items[' + idx + '][to_account_id]" class="form-control student-ajax-select" style="width:100%" required>' +
        '    <option value="">— search student —</option>' +
        '  </select>' +
        '</td>' +
        '<td>' +
        '  <input type="number" name="items[' + idx + '][amount]" class="form-control item-amount"' +
        '         min="1" step="1" placeholder="0" oninput="updateBalance()" required>' +
        '</td>' +
        '<td>' +
        '  <button type="button" class="btn btn-xs btn-danger" onclick="removeItem(' + idx + ')">' +
        '    <i class="fa fa-times"></i>' +
        '  </button>' +
        '</td>';
    tbody.appendChild(tr);

    // Wire Select2 AJAX — identical to TransactionController's account select
    $(tr.querySelector('.student-ajax-select')).select2({
        placeholder: 'Type student name to search…',
        minimumInputLength: 2,
        width: '100%',
        ajax: {
            url: STUDENT_AJAX_URL,
            dataType: 'json',
            delay: 300,
            data: function(params) {
                return { q: params.term };
            },
            processResults: function(response) {
                return { results: response.data || [] };
            },
            cache: true
        }
    });
}

function removeItem(idx) {
    var row = document.getElementById('item-row-' + idx);
    if (row) row.remove();
    updateBalance();
}

// ── Balance checker ──────────────────────────────────────────────────────────
function updateBalance() {
    var remaining  = parseInt(document.getElementById('remaining-amount').value) || 0;
    var splitTotal = 0;
    document.querySelectorAll('.item-amount').forEach(function(el) {
        splitTotal += parseInt(el.value) || 0;
    });

    document.getElementById('bc-remaining').textContent = 'UGX ' + fmt(remaining);
    document.getElementById('bc-split').textContent     = 'UGX ' + fmt(splitTotal);

    var bal = remaining + splitTotal;
    var ok  = originalAmount > 0 && bal === originalAmount;
    var statusEl = document.getElementById('bc-status');
    var submitEl = document.getElementById('submit-btn');

    if (originalAmount === 0) {
        statusEl.textContent = '—'; statusEl.className = ''; submitEl.disabled = true;
    } else if (ok) {
        statusEl.textContent = '✓ Balanced'; statusEl.className = 'text-success'; submitEl.disabled = false;
    } else {
        var diff = bal - originalAmount;
        statusEl.textContent = '✗ Off by ' + (diff > 0 ? '+' : '') + fmt(diff);
        statusEl.className = 'text-danger'; submitEl.disabled = true;
    }
}

function fmt(n) {
    return (n < 0 ? '-' : '') + Math.abs(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}
</script>

<style>
.mt-2 { margin-top: 10px; }
.mt-3 { margin-top: 16px; }
.mb-0 { margin-bottom: 0; }
.mt-1 { margin-top: 6px; }
</style>
