@extends('layouts.app')
@section('title', 'Stock Consumptions')

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    {{-- Font Awesome for icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection

@section('content')
@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif


<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Issue Stock To Users/Departments</h4>
        <a href="{{ route('stockconsumption.create') }}" class="btn btn-primary">Add New</a>
    </div>

    <div class="table-responsive">
        <table id="consumptionTable" class="table table-bordered table-striped align-middle">
            <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Consumption No</th>
                <th>Item</th>
                <th>Quantity</th>
                <th>UOM</th>
                <th>Branch</th>
                <th>Issued To</th>
                <th>Issued By</th>
                <th>Issued On</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($consumptions as $consumption)
                @php
                    // Calculate issued to name (without ID)
                    $issuedToName = 'N/A';
                    if ($consumption->IssuedToType && $consumption->IssuedToID) {
                        $type = \App\Models\Core\Approval\CodeDetail::find($consumption->IssuedToType);
                        if ($type) {
                            $typeName = strtoupper($type->Description);
                            if ($typeName === 'EMPLOYEE') {
                                $user = \App\Models\Auth\User::with('employee')->find($consumption->IssuedToID);
                                if ($user && $user->employee) {
                                    $issuedToName = $user->employee->FirstName . ' ' . $user->employee->LastName;
                                } elseif ($user) {
                                    $issuedToName = $user->UserName;
                                }
                            } elseif ($typeName === 'DEPARTMENT') {
                                $department = \App\Models\HRM\Department::find($consumption->IssuedToID);
                                $issuedToName = optional($department)->Name ?? 'N/A';
                            }
                        }
                    }
                    
                    // Calculate issued by name (without ID)
                    $issuedByName = 'N/A';
                    if ($consumption->issuedBy) {
                        $employee = $consumption->issuedBy->employee;
                        if ($employee) {
                            $issuedByName = $employee->FirstName . ' ' . $employee->LastName;
                        } else {
                            $issuedByName = $consumption->issuedBy->UserName;
                        }
                    }
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $consumption->ConsumptionNo }}</td>
                    <td>{{ optional($consumption->item)->ItemName ?? 'N/A' }}</td>
                    <td>{{ $consumption->Quantity }}</td>
                    <td>{{ optional($consumption->uom)->Code ?? 'N/A' }}</td>
                    <td>{{ optional($consumption->branch)->Name ?? 'N/A' }}</td>
                    <td>{{ $issuedToName }}</td>
                    <td>{{ $issuedByName }}</td>
                    <td>{{ \Carbon\Carbon::parse($consumption->IssuedOn)->format('m/d/Y') }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('stockconsumption.show', $consumption->Id) }}"
                               class="btn btn-sm btn-primary" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('stockconsumption.edit', $consumption->Id) }}" 
                               class="btn btn-sm btn-warning" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-danger" 
                                    onclick="confirmDelete('{{ $consumption->Id }}')" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                            <form id="delete-form-{{ $consumption->Id }}"
                                  action="{{ route('stockconsumption.destroy', $consumption->Id) }}" method="POST"
                                  style="display:none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

    @section('scripts')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script>
            $(document).ready(function () {
                @if(!$consumptions->isEmpty())
                $('#consumptionTable').DataTable({
                    pageLength: 10,
                    ordering: true,
                    searching: true,
                    lengthChange: true,
                    language: {
                        emptyTable: ""
                    }
                });
                @endif
            });

            function confirmDelete(Id) {
                if (confirm('⚠️ Are you sure you want to delete this stock consumption?')) {
                    document.getElementById('delete-form-' + Id).submit();
                }
            }
        </script>
    @endsection
@endsection