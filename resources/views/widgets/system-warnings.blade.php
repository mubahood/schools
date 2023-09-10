<div class="alert alert-danger">
    <h4><i class="icon fa fa-ban"></i> {{ __('System Warnings') }}</h4>
    <ul>
        @foreach ($warnings as $warning)
            <li>{{ $warning }}</li>
        @endforeach
    </ul>
</div>
