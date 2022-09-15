<style>
    .item {
        font-size: 1.5rem;
    }
</style>
<div class="row">
    <div class="col-12 col-md-6">
        <h4 class="m-0"><b>{{ $u->first_name }} {{ $u->last_name }}</b></h4>
        <hr>
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
