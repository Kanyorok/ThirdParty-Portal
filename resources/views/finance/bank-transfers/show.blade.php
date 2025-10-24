@extends('layouts.app')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Transfer #{{ $row->TransferID }}</h4>
        <div class="d-flex gap-2">
            @if($row->Status==='Draft')
                <a href="{{ route('finance.banktransfers.edit',$row->TransferID) }}" class="btn btn-outline-primary">Edit</a>
                <form action="{{ route('finance.banktransfers.post',$row->TransferID) }}" method="POST">@csrf
                    <button class="btn btn-primary">Post</button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-3">
                    <strong>Date:</strong> {{ \Illuminate\Support\Carbon::parse($row->DocDate)->format('Y-m-d') }}</div>
                <div class="col-md-3">
                    <strong>Amount:</strong> {{ number_format($row->Amount,2) }} {{ $row->currency?->Code }}</div>
                <div class="col-md-3"><strong>Rate:</strong> {{ $row->ExchangeRate }}</div>
                <div class="col-md-3"><strong>Status:</strong> <span
                        class="badge bg-{{ $row->Status==='Posted'?'success':($row->Status==='Draft'?'secondary':'danger') }}">{{ $row->Status }}</span>
                </div>
                <div class="col-md-6"><strong>From:</strong> {{ optional($row->fromAccount->bank)->BankName }}
                    — {{ $row->fromAccount?->AccountNumber }}</div>
                <div class="col-md-6"><strong>To:</strong> {{ optional($row->toAccount->bank)->BankName }}
                    — {{ $row->toAccount?->AccountNumber }}</div>
                <div class="col-md-6"><strong>Clearing GL:</strong> {{ $row->ClearingGLAccountID }}</div>
                <div class="col-md-6"><strong>Reference:</strong> {{ $row->Reference }}</div>
                <div class="col-12"><strong>Narration:</strong> {{ $row->Narration }}</div>
            </div>
        </div>
    </div>
@endsection
