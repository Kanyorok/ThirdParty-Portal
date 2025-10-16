@extends('layouts.blanks')
@section('title','Password Recovery')
@section('content')
    <div class="card">
        <div class="card-header text-center pb-0">
            <h1 class="h2">{{ config('app.name') }} @yield('title')</h1>
            <p class="">You are almost there, create your new password,
                make sure it conforms with all your organisation password policy.</p>
        </div>
        <div class="card-body">

            <form method="POST" action="{{ route('password.store') }}" id="recoverAccountForm">
                @csrf <input type="hidden" name="token" value="{{ $request->route('token') }}">
                <div class="mb-3">
                    <label for="email" class="col-form-label text-md-end">{{ __('Email') }}</label>
                    <input id="email" type="text" class="form-control"
                           name="email" value="{{ $request->email }}" required autocomplete="email" autofocus>
                    <span id="email_error" class="invalid-feedback" role="alert"></span>
                </div>
                <div class="mb-3">
                    <label for="password" class="col-form-label text-md-end">{{ __('Password') }}</label>
                    <div class="input-group">
                        <input id="password" type="password" class="form-control" name="password" required
                               autocomplete="new-password">
                        <button type="button" id="togglePassword" class="btn btn-outline-secondary" aria-label="Show password">
                            <i class="fas fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                    <span id="password_error" class="invalid-feedback" role="alert"></span>
                </div>
                <div class="mb-3">
                    <label for="password_confirmation"
                           class="col-form-label text-md-end">{{ __('Confirm Password') }}</label>
                    <div class="input-group">
                        <input id="password_confirmation" type="password" class="form-control" name="password_confirmation"
                               required autocomplete="new-password">
                        <button type="button" id="togglePasswordConfirmation" class="btn btn-outline-secondary" aria-label="Show confirm password">
                            <i class="fas fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                    <span id="password_confirmation_error" class="invalid-feedback" role="alert"></span>
                </div>
                <div class="text-center my-2">
                    <button type="submit" class="btn btn-outline-primary  w-100"
                            id="recoverAccountBtn">{{ __('Reset Password') }}</button>
                </div>

                {{--<div class="row">
                    <div class="col-12">
                        <p id="email_error" class="text-left text-danger d-none error" role="alert"></p>
                        <p id="password_error" class="text-danger text-left d-none error" role="alert"></p>
                        <p id="password_confirmation_error" class="text-danger text-left d-none error"
                           role="alert"></p>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                            <span class="input-group-text"><i
                                                    class="fas fa-envelope-open"></i></span>
                                </div>
                                <input type="email" class="form-control" name="email" id="email"
                                       value="{{ $request->email }}"
                                       placeholder="Email" required="required">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-unlock-alt"></i></span>
                                </div>
                                <input type="password" class="form-control" name="password" id="password"
                                       placeholder="Password" required="required">

                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-unlock-alt"></i></span>
                                </div>
                                <input type="password" class="form-control" name="password_confirmation"
                                       id="password_confirmation"
                                       placeholder="{{ __('Confirm Password') }}" required="required">

                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-bordered w-100 mt-3 mt-sm-4" id=""
                                type="submit">
                            {{ __('Reset Password') }}
                        </button>
                    </div>
                    <div class="col-12">
                        <span class="d-block pt-2 mt-4 border-top">Don't have an account? Download our app and register.</span>
                    </div>
                </div>--}}
            </form>

        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function () {
            $('form#recoverAccountForm').submit(async function (e) {
                e.preventDefault();
                await saveForm($(this), $('#recoverAccountBtn'), true, false, true);
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const pwd = document.getElementById('password');
            const toggle = document.getElementById('togglePassword');
            const pwdConfirm = document.getElementById('password_confirmation');
            const toggleConfirm = document.getElementById('togglePasswordConfirmation');

            function wireToggle(input, button) {
                if (!input || !button) return;
                button.addEventListener('click', function () {
                    const icon = this.querySelector('i');
                    if (input.type === 'password') {
                        input.type = 'text';
                        this.setAttribute('aria-label', 'Hide password');
                        if (icon) { icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash'); }
                    } else {
                        input.type = 'password';
                        this.setAttribute('aria-label', 'Show password');
                        if (icon) { icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye'); }
                    }
                });
            }

            wireToggle(pwd, toggle);
            wireToggle(pwdConfirm, toggleConfirm);
        });
    </script>
@endsection
