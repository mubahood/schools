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
