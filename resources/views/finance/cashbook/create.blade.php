@extends('layouts.app')
@section('title', ($presetType ?? '') === 'PAYMENT' ? 'Cashbook Payment' : 'Cashbook Entry')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
    <style>
        .select2-container {
            width: 100% !important;
        }
        /* Match Bootstrap input (.form-control) height */
        .select2-container--default .select2-selection--single {
            height: calc(2.25rem + 2px);
            min-height: calc(2.25rem + 2px);
            padding: 0.375rem 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            background-color: #fff;
            display: flex;
            align-items: center;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            padding-left: 0;
            padding-right: 2rem; /* space for arrow */
            line-height: 1.5;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100%;
            top: 50%;
            right: 0.75rem;
            transform: translateY(-50%);
        }
        .select2-container--default .select2-selection--single .select2-selection__clear {
            margin-right: 2.25rem;
        }
        .cb-table th, .cb-table td {
            vertical-align: middle;
            white-space: nowrap;
        }
        .cb-table th.col-gl, .cb-table td.col-gl {
            min-width: 360px;
        }
        .cb-table th.col-desc, .cb-table td.col-desc {
            min-width: 420px;
        }
        .cb-table th.col-amt, .cb-table td.col-amt {
            min-width: 200px;
            text-align: right;
        }
    </style>
@endsection

@section('content')
    <div class="container my-3">
        <div class="card shadow-sm rounded-3">
            <div class="card-header bg-light py-2 px-3 d-flex align-items-center">
                <h6 class="mb-0 text-muted">
                    <i class="fab fa-wpforms text-info me-1"></i>
                    {{ ($presetType ?? '') === 'PAYMENT' ? 'Cashbook Payment' : 'Cashbook Entry' }}
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('cashbook.store') }}" method="POST" id="cashbookForm">
                    @csrf
                    @include('finance.cashbook._form', ['entry' => null])
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof window.initCashbookSelect2 === 'function') {
                window.initCashbookSelect2();
            }
        });
    </script>
@endsection
