@extends('layouts.blanks')

@section('title','Sign in')

@section('content')
    <div class="auth-wrapper v2">
        <div class="auth-form">
            <div class="card my-5">
                <form method="POST" action="{{ route('login') }}" class="card-body">@csrf
                    <div class="text-center"><img src="{{ asset('assets/img/CBT-Logo.jpg') }}" height="100" width="120"
                                                  alt="">
                    </div>
                    <h4 class="text-center f-w-500 mb-3 mt-lg-5">Login with your USERID or Email </h4>
                    {{-- Branch Selection FIRST --}}
                    <div class="mb-3">
                        <label for="branch" class="form-label">Login Branch <span class="text-danger">*</span></label>
                        <select class="form-control @error('branch') is-invalid @enderror" id="branch" name="branch"
                                required>
                            <option disabled {{ old('branch') ? '' : 'selected' }}>-- Select Branch --</option>
                            @foreach ($branches as $branch)
                                <option
                                    value="{{ $branch->BranchID }}" {{ old('branch') === $branch->BranchID ? 'selected' : '' }}>
                                    {{ $branch->Name }}
                                </option>
                            @endforeach
                        </select>
                        @error('branch')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <input id="UserID" type="text" class="form-control @error('UserID') is-invalid @enderror"
                               name="UserID" value="{{ old('UserID') }}" required autocomplete="UserID" autofocus
                               style="text-transform: uppercase;" placeholder="UserID or Email Address">
                        @error('UserID')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong> </span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <div class="input-group">
                            <input id="password" type="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   name="password" required autocomplete="current-password" placeholder="Password">
                            <button type="button" id="togglePassword" class="btn btn-outline-secondary" aria-label="Show password">
                                <i class="fas fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                    {{--<div class="d-flex mt-1 justify-content-between align-items-center">
                        <div class="form-check">
                            <input class="form-check-input input-primary" type="checkbox"
                                                       id="customCheckc1" checked=""> <label
                                class="form-check-label text-muted" for="customCheckc1">Remember me?</label></div>
                        <h6 class="text-secondary f-w-400 mb-0"><a href="forgot-password-v2.html">Forgot Password?</a>
                        </h6>
                    </div>--}}
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                    {{--<div class="d-flex justify-content-between align-items-end mt-4"><h6 class="f-w-500 mb-0">Don't have
                            an Account?</h6><a href="register-v2.html" class="link-primary">Create Account</a></div>--}}
                </form>
            </div>
        </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const pwd = document.getElementById('password');
            const btn = document.getElementById('togglePassword');
            if (!pwd || !btn) return;

            btn.addEventListener('click', function () {
                const icon = this.querySelector('i');
                if (pwd.type === 'password') {
                    pwd.type = 'text';
                    this.setAttribute('aria-label', 'Hide password');
                    if (icon) {
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    }
                } else {
                    pwd.type = 'password';
                    this.setAttribute('aria-label', 'Show password');
                    if (icon) {
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                }
            });
        });
    </script>
@endsection



