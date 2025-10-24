@extends('layouts.app')

@section('content')
    <h4 class="mb-3">Edit Cheque Book #{{ $row->ChequeBookID }}</h4>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Fix the following:</strong>
            <ul class="mb-0">@foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('finance.chequebooks.update',$row->ChequeBookID) }}">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Bank Account <span class="text-danger">*</span></label>
                <select name="BankAccountID" class="form-select" required>
                    @foreach($bankAccounts as $ba)
                        <option
                            value="{{ $ba->AccountID }}" @selected(old('BankAccountID',$row->BankAccountID)==$ba->AccountID)>
                            {{ optional($ba->bank)->BankName }} — {{ $ba->AccountNumber }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Book Name</label>
                <input name="BookName" class="form-control" value="{{ old('BookName',$row->BookName) }}">
            </div>

            <div class="col-md-2">
                <label class="form-label">Prefix</label>
                <input name="Prefix" class="form-control" value="{{ old('Prefix',$row->Prefix) }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Suffix</label>
                <input name="Suffix" class="form-control" value="{{ old('Suffix',$row->Suffix) }}">
            </div>

            <div class="col-md-3">
                <label class="form-label">Start Number <span class="text-danger">*</span></label>
                <input type="number" name="StartNumber" class="form-control"
                       value="{{ old('StartNumber',$row->StartNumber) }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">End Number <span class="text-danger">*</span></label>
                <input type="number" name="EndNumber" class="form-control"
                       value="{{ old('EndNumber',$row->EndNumber) }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Next Leaf Number <span class="text-danger">*</span></label>
                <input type="number" name="NextLeafNumber" class="form-control"
                       value="{{ old('NextLeafNumber',$row->NextLeafNumber) }}" required>
                <small class="text-muted">Must be within the start/end range.</small>
            </div>

            <div class="col-md-3">
                <label class="form-label">Active</label>
                <select name="IsActive" class="form-select">
                    <option value="1" @selected(old('IsActive',$row->IsActive)==1)>Yes</option>
                    <option value="0" @selected(old('IsActive',$row->IsActive)==0)>No</option>
                </select>
            </div>
        </div>

        <hr class="my-4">
        <div class="d-flex gap-2">
            <button class="btn btn-success">Save</button>
            <a href="{{ route('finance.chequebooks.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
