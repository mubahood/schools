<style>
    .item {
        font-size: 1.5rem;
    }
</style>
@include('admin.dashboard.show-user-profile-header', ['u' => $u])
<div class="row">
    <div class="col-12 col-md-6"> 
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
    <div class="col-12 col-md-6"><img class="img img-fluid" src="{{ $u->avatar }}" alt=""></div>
</div>
