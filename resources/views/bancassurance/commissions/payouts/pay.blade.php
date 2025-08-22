@extends('layouts.app')
@section('title', 'Initiate Commission Payout')

@section('content')
<div class="container mt-4">

    <form method="POST" action="{{ route('bancassurance.commissions.payouts.store') }}" enctype="multipart/form-data">
        @csrf
        
    <div class="mb-3">
        <label class="form-label">PolicyId <span class="text-danger">*</span></label>
            <select name="PolicyId" class="form-select" required>
            <option value="">--Select a policy--</option>
              @foreach ($policies as $policy)
                <option value="{{ $policy->Id }}">
                  {{ $policy->PolicyNumber }}
                </option>
              @endforeach
            </select>
          </div>

        <div class="mb-3">
            <label class="form-label">Payout Reference <span class="text-danger">*</span></label>
            <input type="text" name="PayoutReference" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Amount <span class="text-danger">*</span></label>
            <input type="number" name="PaidAmount" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Payment Date <span class="text-danger">*</span></label>
            <input type="date" name="PaymentDate" class="form-control" value="{{ now()->format('d/m/Y') }}" required>
        </div>

        <div class="mb-3">
         <label class="form-label">Payment Mode <span class="text-danger">*</span></label>
            <select name="PaymentMode" class="form-select" required>
            <option value="">--Select a status--</option>
              @foreach ($paymentmodes as $paymentmode)
                <option value="{{ $paymentmode->ID }}">
                  {{ $paymentmode->Description }}
                </option>
              @endforeach
            </select>
          </div>
        <div class="mb-3">
            <label class="form-label">Paid By <span class="text-danger">*</span></label>
            <select name="PaidBy" class="form-select" required>
            <option value="">--Select a user--</option>
              @foreach ($paidBy as $user)
                <option value="{{ $user->Id }}">
                  {{ $user->Name }}
                </option>
              @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Remarks <span class="text-danger">*</span></label>
            <textarea name="Remarks" class="form-control" rows="2"></textarea>
        </div>

        <div class="text-end">
            <button class="btn btn-success">Submit Payout</button>
        </div>
    </form>
</div>
@endsection
