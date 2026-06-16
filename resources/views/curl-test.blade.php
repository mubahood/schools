<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>cURL Tester</title>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #eef0f4;
            color: #333;
            min-height: 100vh;
            padding: 40px 16px;
        }
        .wrap { max-width: 860px; margin: 0 auto; }

        /* ── Header ── */
        .page-title { font-size: 22px; font-weight: 700; margin-bottom: 4px; }
        .page-sub   { font-size: 13px; color: #888; margin-bottom: 24px; }

        /* ── Card ── */
        .card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,.08);
            padding: 24px;
            margin-bottom: 20px;
        }

        /* ── URL row ── */
        .url-row { display: flex; gap: 8px; margin-bottom: 16px; }
        .method-tag {
            background: #f0f4ff;
            color: #4361ee;
            border: 1px solid #c8d4ff;
            border-radius: 6px;
            padding: 0 14px;
            font-size: 13px;
            font-weight: 700;
            display: flex;
            align-items: center;
            white-space: nowrap;
        }
        #url-input {
            flex: 1;
            border: 1.5px solid #dde1e9;
            border-radius: 6px;
            padding: 10px 14px;
            font-size: 14px;
            font-family: 'Menlo', 'Consolas', monospace;
            outline: none;
            transition: border-color .2s;
        }
        #url-input:focus { border-color: #4361ee; }
        #send-btn {
            background: #4361ee;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 10px 28px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background .15s;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 7px;
        }
        #send-btn:hover    { background: #3451d1; }
        #send-btn:disabled { background: #8fa4f8; cursor: not-allowed; }
        #send-btn .btn-spin {
            display: none;
            width: 14px; height: 14px;
            border: 2px solid rgba(255,255,255,.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }
        #send-btn.loading .btn-spin  { display: inline-block; }
        #send-btn.loading .btn-label { display: none; }

        /* ── Quick buttons ── */
        .quick-row { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; }
        .quick-label { font-size: 12px; color: #aaa; margin-right: 2px; white-space: nowrap; }
        .quick-url {
            background: #f5f6fa;
            border: 1px solid #e0e3ec;
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 12px;
            cursor: pointer;
            transition: background .15s, border-color .15s, color .15s;
            color: #555;
        }
        .quick-url:hover        { background: #e8ecff; border-color: #b0bef8; color: #4361ee; }
        .quick-url.selected     { background: #4361ee; border-color: #4361ee; color: #fff; }

        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Empty state ── */
        #empty-state {
            text-align: center;
            padding: 48px 0 32px;
            color: #bbb;
        }
        #empty-state svg { margin-bottom: 12px; opacity: .4; }
        #empty-state p   { font-size: 14px; }

        /* ── Response card ── */
        #response-card { display: none; }

        .resp-header {
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid #f0f1f5;
            padding-bottom: 14px;
            margin-bottom: 18px;
            flex-wrap: wrap;
        }
        .status-badge {
            font-size: 13px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 20px;
            color: #fff;
        }
        .s-2xx { background: #22c55e; }
        .s-3xx { background: #f59e0b; }
        .s-4xx { background: #ef4444; }
        .s-5xx { background: #7c3aed; }
        .s-err { background: #6b7280; }

        .meta-pill {
            font-size: 12px;
            color: #666;
            background: #f5f6fa;
            border-radius: 20px;
            padding: 3px 10px;
        }
        .meta-url {
            font-size: 12px;
            color: #999;
            font-family: monospace;
            margin-left: auto;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 320px;
        }

        /* ── Tabs ── */
        .tabs { display: flex; border-bottom: 2px solid #f0f1f5; margin-bottom: 14px; }
        .tab-btn {
            padding: 8px 18px;
            font-size: 13px;
            font-weight: 600;
            color: #999;
            background: none;
            border: none;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            cursor: pointer;
            transition: color .15s, border-color .15s;
        }
        .tab-btn.active { color: #4361ee; border-bottom-color: #4361ee; }
        .tab-pane { display: none; }
        .tab-pane.active { display: block; }

        /* ── Code output ── */
        #body-output, #headers-output {
            background: #1a1d2e;
            font-family: 'Menlo', 'Consolas', monospace;
            border-radius: 8px;
            padding: 16px;
            overflow: auto;
            white-space: pre-wrap;
            word-break: break-all;
            line-height: 1.6;
        }
        #body-output    { color: #cdd6f4; font-size: 12.5px; max-height: 520px; }
        #headers-output { color: #89dceb; font-size: 12px;   max-height: 300px; white-space: pre; }

        /* ── Error banner ── */
        #error-alert {
            display: none;
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            border-radius: 6px;
            padding: 10px 14px;
            font-size: 13px;
            margin-bottom: 14px;
        }

        /* ── Hint text under input ── */
        .hint {
            font-size: 12px;
            color: #b0b5c0;
            margin-top: 6px;
            display: none;
        }
        .hint.visible { display: block; }
    </style>
</head>
<body>
<div class="wrap">

    <div class="page-title">cURL Tester</div>
    <div class="page-sub">Send a GET request to any URL and inspect the response</div>

    <div class="card">
        <div class="url-row">
            <div class="method-tag">GET</div>
            <input type="text" id="url-input"
                   placeholder="https://example.com/api/endpoint"
                   autocomplete="off" spellcheck="false">
            <button id="send-btn">
                <span class="btn-label">Send</span>
                <span class="btn-spin"></span>
            </button>
        </div>
        <div class="hint" id="hint">Press <kbd style="background:#f0f4ff;border:1px solid #c8d4ff;border-radius:3px;padding:1px 5px;font-size:11px;color:#4361ee">Enter</kbd> or click <strong>Send</strong> to fire the request</div>

        <div class="quick-row">
            <span class="quick-label">Quick:</span>
            @php
            $quick = [
                'https://schoolpay.co.ug'                     => 'SchoolPay',
                'https://google.com'                          => 'Google',
                'https://httpbin.org/get'                     => 'httpbin /get',
                'https://httpbin.org/json'                    => 'httpbin /json',
                'https://httpbin.org/status/404'              => '404 status',
                'https://httpbin.org/status/500'              => '500 status',
                'https://api.ipify.org'                       => 'My IP',
                'https://jsonplaceholder.typicode.com/todos/1'=> 'JSONPlaceholder',
                'https://httpbin.org/delay/2'                 => 'Slow (2s)',
            ];
            @endphp
            @foreach($quick as $url => $label)
            <button class="quick-url" data-url="{{ $url }}">{{ $label }}</button>
            @endforeach
        </div>
    </div>

    {{-- Empty / idle state shown before any request is fired --}}
    <div id="empty-state">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#4361ee" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/>
            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10A15.3 15.3 0 0 1 12 2z"/>
        </svg>
        <p>Enter a URL above and press <strong>Send</strong> to see the response</p>
    </div>

    <div id="response-card" class="card">
        <div class="resp-header">
            <span id="status-badge" class="status-badge"></span>
            <span id="meta-time"  class="meta-pill" style="display:none"></span>
            <span id="meta-size"  class="meta-pill" style="display:none"></span>
            <span id="meta-type"  class="meta-pill" style="display:none"></span>
            <span id="meta-url"   class="meta-url"></span>
        </div>

        <div id="error-alert"></div>

        <div class="tabs">
            <button class="tab-btn active" data-tab="tab-body">Body</button>
            <button class="tab-btn"        data-tab="tab-headers">Headers</button>
        </div>

        <div class="tab-pane active" id="tab-body">
            <div id="body-output"></div>
        </div>
        <div class="tab-pane" id="tab-headers">
            <div id="headers-output"></div>
        </div>
    </div>

</div>

<script>
$(function () {

    // ── Tab switching ──────────────────────────────────────────────
    $(document).on('click', '.tab-btn', function () {
        const target = $(this).data('tab');
        $('.tab-btn').removeClass('active');
        $('.tab-pane').removeClass('active');
        $(this).addClass('active');
        $('#' + target).addClass('active');
    });

    // ── Quick-fill: populate input only, do NOT send ───────────────
    $(document).on('click', '.quick-url', function () {
        const url = $(this).data('url');
        $('#url-input').val(url).focus();
        $('.quick-url').removeClass('selected');
        $(this).addClass('selected');
        $('#hint').addClass('visible');
    });

    // Clear selected pill when user manually edits the input
    $('#url-input').on('input', function () {
        const val = $(this).val().trim();
        $('.quick-url').each(function () {
            if ($(this).data('url') === val) {
                $(this).addClass('selected');
            } else {
                $(this).removeClass('selected');
            }
        });
        if (val) $('#hint').addClass('visible');
        else     $('#hint').removeClass('visible');
    });

    // ── Send on Enter ──────────────────────────────────────────────
    $('#url-input').on('keydown', function (e) {
        if (e.key === 'Enter') send();
    });

    $('#send-btn').on('click', send);

    // ── Helpers ───────────────────────────────────────────────────
    function statusClass(code) {
        if (code >= 200 && code < 300) return 's-2xx';
        if (code >= 300 && code < 400) return 's-3xx';
        if (code >= 400 && code < 500) return 's-4xx';
        if (code >= 500)               return 's-5xx';
        return 's-err';
    }

    function tryPrettyJson(text) {
        try { return JSON.stringify(JSON.parse(text), null, 2); } catch (e) { return text; }
    }

    function setPill(id, value) {
        if (value) { $('#' + id).text(value).show(); }
        else       { $('#' + id).hide(); }
    }

    // ── Core send ─────────────────────────────────────────────────
    function send() {
        const url = $('#url-input').val().trim();
        if (!url) { $('#url-input').focus(); return; }

        $('#hint').removeClass('visible');
        $('#empty-state').hide();
        $('#response-card').hide();
        $('#send-btn').prop('disabled', true).addClass('loading');

        $.ajax({
            url: '{{ url("curl-test") }}',
            method: 'POST',
            data: { url: url, _token: '{{ csrf_token() }}' },
            success: show,
            error: function (xhr) { show(xhr.responseJSON || { error: xhr.statusText, http_code: 0 }); },
            complete: function () {
                $('#send-btn').prop('disabled', false).removeClass('loading');
            }
        });
    }

    // ── Render response ───────────────────────────────────────────
    function show(r) {
        $('#response-card').show();
        $('#error-alert').hide().text('');

        const code = r.http_code || 0;
        $('#status-badge')
            .text(code ? 'HTTP ' + code : 'Error')
            .attr('class', 'status-badge ' + statusClass(code));

        setPill('meta-time', r.total_time  || '');
        setPill('meta-size', r.size        || '');
        setPill('meta-type', r.content_type|| '');

        $('#meta-url').text(r.url || '').attr('title', r.url || '');

        if (r.curl_error) {
            $('#error-alert').show().text('cURL error: ' + r.curl_error);
        }

        $('#body-output').text(tryPrettyJson(r.body || ''));
        $('#headers-output').text(r.headers || '');

        // always reset to body tab
        $('.tab-btn').removeClass('active');
        $('.tab-pane').removeClass('active');
        $('.tab-btn[data-tab="tab-body"]').addClass('active');
        $('#tab-body').addClass('active');
    }

});
</script>
</body>
</html>
