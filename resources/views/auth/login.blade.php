@extends('layouts.blanks')

@section('title','Sign in')

@section('content')
    <div class="card">
        <div class="card-header text-center">
            <h1 class="h2">Welcome to {{ config('app.name') }}</h1>
            <small class="lead">Authorized persons only</small>
        </div>
        <div class="card-body">
            <div class="m-sm-4">
                <form method="POST" action="{{ route('login') }}">@csrf
                    <div class="mb-3">
                        <label for="UserID" class="col-form-label text-md-end">{{ __('User ID') }}</label>
                        <input id="UserID" type="text" class="form-control @error('UserID') is-invalid @enderror"
                               name="UserID" value="{{ old('UserID') }}" required autocomplete="UserID" autofocus
                               style="text-transform: uppercase;">
                        @error('UserID')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong> </span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="password" class="col-form-label text-md-end">{{ __('Password') }}</label>

                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                        @error('password')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                    <div class="text-center my-2">
                        <button type="submit" class="btn btn-outline-primary  w-100">Sign in</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
