@if (session()->has('errors'))
    <div class="alert alert-danger">
        @foreach (array_unique( $errors->all() ) as $error)
            {{ $error }}<br>
        @endforeach
    </div>
@endif

@if(session()->has('error'))
    <div class="alert alert-danger">
        {!! session()->get('error') !!}
    </div>
@endif

@if(session()->has('success'))
    <div class="alert alert-success">
        {!! session()->get('success') !!}
    </div>
@endif
