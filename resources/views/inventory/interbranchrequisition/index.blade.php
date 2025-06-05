@extends('layouts.app')

@section('title', 'Inter-Branch Requisition')

@section('content')
@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="container mt-4">
  <h4 class="mb-3">Inter-Branch Requisition List</h4>

  <a href="{{ route('interbranchrequisition.create') }}" class="btn btn-sm btn-success mb-3">➕ Add Requisition</a>

  <div class="card shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="requisitionTable" class="table table-bordered table-striped align-middle">
        <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Requisition No</th>
              <th>From Branch</th>
              <th>To Branch</th>
              <th>Date</th>
              <th>Status</th>
              <th>Items</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($groupedRequisitions as $requisition)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $requisition->ReqNo ?? '-' }}</td>
                <td>{{ $requisition->fromBranch->Name ?? '-' }}</td>
                <td>{{ $requisition->toBranch->Name ?? '-' }}</td>
                <td>{{ \Carbon\Carbon::parse($requisition->CreatedOn)->format('Y-m-d') }}</td>
                <td>
                  @if($requisition->Status === 'Approved')
                    <span class="badge text-bg-success">Approved</span>
                  @elseif($requisition->Status === 'Pending Approval' || $requisition->Status === 'Submitted')
                    <span class="badge bg-warning">Pending Approval</span>
                  @elseif($requisition->Status === 'Rejected')
                    <span class="badge bg-danger">Rejected</span>
                  @else
                    <span class="badge bg-secondary">{{ $requisition->Status }}</span>
                  @endif
                </td>
                <td>{{ $requisition->items->count() }}</td>
                <td>
                  <a href="{{ route('interbranchrequisition.show', $requisition->Id) }}"
                     class="btn btn-secondary btn-sm"
                     onclick="@if($requisition->Status !== 'Pending Approval' && $requisition->Status !== 'Submitted') return actionError(event); @endif">
                    View
                  </a>
                  <a href="{{ route('interbranchrequisition.edit', $requisition->Id) }}"
                     class="btn btn-warning btn-sm"
                     onclick="@if($requisition->Status !== 'Pending Approval' && $requisition->Status !== 'Submitted') return actionError(event); @endif">
                    Edit
                  </a>
                  <a href="#"
                     class="btn btn-danger btn-sm"
                     onclick="@if($requisition->Status !== 'Pending Approval' && $requisition->Status !== 'Submitted') return actionError(event); @else confirmDelete('{{ $requisition->Id }}'); return false; @endif">
                    Delete
                  </a>
                  <form id="delete-form-{{ $requisition->Id }}" action="{{ route('interbranchrequisition.destroy', $requisition->Id) }}" method="POST" style="display:none;">
                    @csrf
                    @method('DELETE')
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

  <script>
    $(document).ready(function () {
        $('#requisitionTable').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });
    });

    function confirmDelete(Id) {
        if (confirm('⚠️ Are you sure you want to delete this requisition?')) {
            document.getElementById('delete-form-' + Id).submit();
        }
    }

    function actionError(event) {
        alert('You cannot perform this action because a decision has already been made on this requisition.');
        if(event) event.preventDefault();
        return false;
    }
  </script>
@endsection