@extends('layouts.app')
@section('title', 'Create Bank Account')
@section('content')
<div class="container mt-0">
    <div class="card shadow rounded-4">
        <div class="card-header bg-light py-2 px-3 d-flex align-items-center">
            <h6 class="mb-0 text-muted">
                <i class="fab fa-wpforms text-info"></i>
            </h6>
        </div>

        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger rounded-3">
                    <strong>Please fix the following:</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('finance.bankaccountsetup.store') }}" method="POST" novalidate>
                @csrf

                <div class="card mb-3 border-0 shadow-sm rounded-3">
                    <div class="card-header bg-light py-2 px-3">
                        <h6 class="mb-0 text-primary"><i class="fas fa-university me-1"></i> Account Details</h6>
                    </div>
                    <div class="card-body">
                        @include('finance.bankaccountsetup._form', ['account' => null])
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="{{ route('finance.bankaccountsetup.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-success"
                            onclick="if(this.form.checkValidity()){
                                this.disabled = true;
                                this.innerHTML = '<i class=&quot;fas fa-spinner fa-spin me-1&quot;></i> Please Wait...';
                                this.form.submit();
                            }">
                        <i class="fas fa-save me-1"></i>
                        Save Account
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection
