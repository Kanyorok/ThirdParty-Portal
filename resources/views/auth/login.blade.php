@extends('layouts.blanks')

@section('title','Sign in')

@section('content')
    <div class="auth-wrapper v2">
        <div class="auth-form ">
            <div class="card my-5 border border-primary border-1 shadow-lg">
                <form method="POST" action="{{ route('login') }}" class="card-body" id="LoginForm">@csrf
                    @php
                        $org = \App\Models\Settings\APICredential::query()->where('Integration', \App\Enums\Core\IntegrationsEnum::Organization->value)->latest('Id')->first();
                        $branding = $org?->Configuration;
                        $logo = is_object($branding) && isset($branding->logo) ? $branding->logo : 'assets/img/BRERP_Logo_small.png';

                    @endphp
                    <div class="text-center mb-4">
                        <img src="{{ asset($logo) }}" alt="Logo" style="max-width: 100%; height: auto; max-height: 120px; object-fit: contain; margin-bottom: 12px;">
                    </div>
                    <h4 class="text-center f-w-500 mb-3">Welcome Back Sign In </h4>
                    <div class="mb-3">
                        <label for="branch" class="form-label">Login Branch <span class="text-danger">*</span></label>
                        <select class="form-control" id="branch" name="branch"
                                required>
                            <option disabled {{ old('branch') ? '' : 'selected' }}>-- Select Branch --</option>
                            @foreach ($branches as $branch)
                                <option
                                    value="{{ $branch->BranchID }}" {{ old('branch') === $branch->BranchID ? 'selected' : '' }}>
                                    {{ $branch->Name }}
                                </option>
                            @endforeach
                        </select>
                        <p id="branch_error" class="invalid-feedback d-none fs-5 error col-12" role="alert"></p>
                    </div>
                    <div class="mb-3">
                        <label for="UserID" class="form-label">UserID or Email Address <span
                                class="text-danger">*</span></label>
                        <input id="UserID" type="text" class="form-control"
                               name="UserID" value="{{ old('UserID') }}" required autocomplete="UserID" autofocus
                               style="text-transform: uppercase;" placeholder="UserID or Email Address">
                        <p id="UserID_error" class="invalid-feedback d-none error fs-5 col-12" role="alert"></p>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input id="password" type="password"
                                   class="form-control"
                                   name="password" required autocomplete="current-password" placeholder="Password">
                            <button type="button" id="togglePassword" class="btn btn-outline-secondary" aria-label="Show password">
                                <i class="fas fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                        <p id="password_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                    </div>
                    <div class="d-grid my-4">
                        <button type="submit" class="btn btn-primary" id="LoginBtn">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('form#LoginForm').submit(async function (e) {
                e.preventDefault();
                await saveForm($(this), $('#LoginBtn'), true, true, true);
            });

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



