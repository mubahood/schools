<style>
    .item {
        font-size: 1.5rem;
    }
</style>
@include('admin.dashboard.show-user-profile-header', ['u' => $u])
<div class="row">
    <div class="col-sm-12 col-md-12">
        @if (empty($u->classes))
            <div class="alert alert-info">This student no class.</div>
        @else
            <ul>
                @foreach ($u->classes as $class)
                    <li>
                        <p>{{ $class->class->name }}</p>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
