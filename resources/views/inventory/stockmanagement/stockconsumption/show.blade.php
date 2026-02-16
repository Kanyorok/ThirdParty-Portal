@extends('layouts.app')

@section('title', 'View Stock Consumption')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Stock Consumption Details</h4>
        <a href="{{ route('stockconsumption.index') }}" class="btn btn-secondary">Back</a>
    </div>

    <div class="card">
        <div class="card-body row">
            <div class="col-md-6 mb-3">
                <strong>Consumption No:</strong> {{ $consumption->ConsumptionNo }}
            </div>

            <div class="col-md-6 mb-3">
                <strong>Item:</strong> {{ optional($consumption->item)->ItemName ?? 'N/A' }}
            </div>

            <div class="col-md-6 mb-3">
                <strong>Quantity:</strong> {{ $consumption->Quantity }}
            </div>

            <div class="col-md-6 mb-3">
                <strong>Unit of Measure:</strong> {{ optional($consumption->uom)->Code ?? 'N/A' }}
            </div>

            <div class="col-md-6 mb-3">
                <strong>Branch:</strong> {{ optional($consumption->branch)->Name ?? 'N/A' }}
            </div>

            <div class="col-md-6 mb-3">
                <strong>Store:</strong> {{ optional($consumption->store)->StoreName ?? 'N/A' }}
            </div>

            <div class="col-md-6 mb-3">
                @php
                    $issuedToName = 'N/A';
                    if ($consumption->IssuedToType && $consumption->IssuedToID) {
                        $type = \App\Models\Core\Approval\CodeDetail::find($consumption->IssuedToType);
                        if ($type) {
                            $typeName = strtoupper($type->Description);
                            if ($typeName === 'EMPLOYEE') {
                                $user = \App\Models\Auth\User::with('employee')->find($consumption->IssuedToID);
                                if ($user && $user->employee) {
                                    $issuedToName = $user->employee->FirstName . ' ' . $user->employee->LastName;
                                    if ($user->employee->EmployeeID) {
                                        $issuedToName .= ' (' . $user->employee->EmployeeID . ')';
                                    }
                                } elseif ($user) {
                                    $issuedToName = $user->UserName;
                                }
                            } elseif ($typeName === 'DEPARTMENT') {
                                $department = \App\Models\HRM\Department::find($consumption->IssuedToID);
                                $issuedToName = optional($department)->Name ?? 'N/A';
                            }
                        }
                    }
                    
                    $issuedToType = \App\Models\Core\Approval\CodeDetail::find($consumption->IssuedToType);
                @endphp
                <strong>Issued To:</strong> {{ $issuedToName }}
                @if($issuedToType)
                    <br><small class="text-muted">Type: {{ $issuedToType->Description }}</small>
                @endif
            </div>

            <div class="col-md-6 mb-3">
                @php
                    $issuedByName = 'N/A';
                    if ($consumption->issuedBy) {
                        $employee = $consumption->issuedBy->employee;
                        if ($employee) {
                            $issuedByName = $employee->FirstName . ' ' . $employee->LastName;
                            if ($employee->EmployeeID) {
                                $issuedByName .= ' (' . $employee->EmployeeID . ')';
                            }
                        } else {
                            $issuedByName = $consumption->issuedBy->UserName;
                        }
                    }
                @endphp
                <strong>Issued By:</strong> {{ $issuedByName }}
            </div>

            <div class="col-md-12 mb-3">
                <strong>Remarks:</strong> 
                @if($consumption->Remarks)
                    <div class="mt-2 p-3 bg-light rounded">{{ $consumption->Remarks }}</div>
                @else
                    <span class="text-muted">N/A</span>
                @endif
            </div>
        </div>
    </div>

    <div class="mt-3 d-flex gap-2">
        <a href="{{ route('stockconsumption.edit', $consumption->Id) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> Edit
        </a>
        <form action="{{ route('stockconsumption.destroy', $consumption->Id) }}" method="POST" 
              onsubmit="return confirm('⚠️ Are you sure you want to delete this stock consumption?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash"></i> Delete
            </button>
        </form>
    </div>
</div>
@endsection

@section('styles')
<style>
    .card {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
    }
    
    .card-body strong {
        color: #495057;
        min-width: 150px;
        display: inline-block;
    }
    
    .bg-light {
        background-color: #f8f9fa !important;
    }
    
    .text-muted {
        color: #6c757d !important;
    }
</style>
@endsection