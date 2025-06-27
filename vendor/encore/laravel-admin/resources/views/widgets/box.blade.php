<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');

    .modern-box {
        background: #fff;
        border-radius: 16px;
        box-shadow:
            0 4px 16px rgba(60, 72, 100, 0.12),
            0 1px 4px rgba(60, 72, 100, 0.05);
        margin-bottom: 18px;
        overflow: hidden;
        border: none;
        transition: box-shadow 0.22s, border 0.22s;
        font-family: 'Inter', Arial, Helvetica, sans-serif;
    }

    .modern-box:hover {
        box-shadow:
            0 8px 32px rgba(60, 72, 100, 0.16),
            0 2px 8px rgba(60, 72, 100, 0.08);
        border: 1.5px solid #60a5fa;
    }

    .modern-box-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 18px 8px 18px;
        background: linear-gradient(90deg, #f1f5f9 0%, #bae6fd 100%);
        border-bottom: 1px solid #e5e7eb;
        transition: background 0.22s;
    }

    .modern-box:hover .modern-box-header {
        background: linear-gradient(90deg, #dbeafe 0%, #60a5fa 100%);
    }

    .modern-box-title {
        font-size: 1.15rem;
        font-weight: 600;
        color: #1e293b;
        margin: 0;
        letter-spacing: 0.3px;
        font-family: 'Inter', Arial, Helvetica, sans-serif;
        transition: color 0.22s;
    }

    .modern-box:hover .modern-box-title {
        color: #2563eb;
    }

    .modern-box-tools {
        display: flex;
        gap: 8px;
    }

    .modern-box-body {
        padding: 14px 18px;
        font-size: 1.2rem;
        color: #334155;
        background: #fff;
        font-family: 'Inter', Arial, Helvetica, sans-serif;
    }

    .modern-box-footer {
        padding: 10px 18px;
        background: #f1f5f9;
        border-top: 1px solid #e5e7eb;
        color: #64748b;
        font-size: 1rem;
        font-family: 'Inter', Arial, Helvetica, sans-serif;
    }
</style>

<div class="modern-box" {!! $attributes !!}>
    @if ($title || $tools)
        <div class="modern-box-header">
            <h3 class="modern-box-title">{{ $title }}</h3>
            <div class="modern-box-tools">
                @foreach ($tools as $tool)
                    {!! $tool !!}
                @endforeach
            </div>
        </div>
    @endif
    <div class="modern-box-body">
        {!! $content !!}
    </div>
    @if ($footer)
        <div class="modern-box-footer">
            {!! $footer !!}
        </div>
    @endif
</div>
<script>
    {!! $script !!}
</script>
