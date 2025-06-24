@extends('layouts.app')
@section('title', 'New Budget Line & GL Mapping')
@section('content')
@if ($errors->any())
    <div class="alert alert-danger">
        <strong>There were some errors with your submission:</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif  
  <div class="mb-2 d-flex justify-content-between">
   <a href="{{ route('budgetlinemapping.create') }}" class="btn btn-success btn-sm" data-bs-toggle="modal1" data-bs-target="#addLineModal">
    + New Budget Line</a>
  </div>
<div class="card mb-4">
<div class="card-header bg-secondary text-white">📄 Budget Lines List</div>
  <div class="card-body">
<p class="text-muted">
  Below is a list of all existing Budget Lines, including their associated departments, descriptions, mapped CBS GL accounts, and product type mappings (if projection-driven). 
</p>
<div class="table-responsive">
  <table class="table table-bordered table-hover text-nowrap text-center">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Line Name</th>
        <th>Department</th>
        <th>GL Account Type</th>
        <th>GL Sub-Type</th>
        <th>Is Product Driven</th>
        <th>Description</th>
        <th>CBS GLs Mapped</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($budgetLines as $item)
          <tr>
            <td>{{ $loop->index+1 }}.</td>
            <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $item->LineName }}">
              {{ $item->LineName }}
            </td>
            <td>{{ $item->department->Name ?? 'N/A' }}</td>
            <td>{{ $item->glAccountType->Description ?? 'N/A' }}</td>
            <td>{{ $item->glAccountSubType->GLAccountSubTypeName ?? 'N/A' }}</td>
            <td>
              @if($item->IsProductDriven)
                <span class="badge bg-success">Yes</span>
              @else
                <span class="badge bg-secondary">No</span>
              @endif
            </td>
            <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $item->Description }}">
              {{ $item->Description }}
            </td>
            <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
              @foreach($item->glAccounts as $gl)
                <small><div>GL{{ $gl->Id }} - {{ $gl->Description }}</div></small>
              @endforeach
            </td>
            <td>
              <a href="{{ route('budgetlinemapping.edit', $item->Id) }}" class="btn btn-sm btn-info">✏️</a>
                <form method="POST" action="{{ route('budgetlinemapping.destroy',$item->Id) }}" class="delete-form d-inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger delete-btn">🗑 Delete</button>
              </form>
            </td>
          </tr>        
      @endforeach
    </tbody>
  </table>
</div>

  </div>
</div>


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const deleteForms = document.querySelectorAll('.delete-form');

            deleteForms.forEach(form => {
                form.addEventListener('submit', function (e) {
                    e.preventDefault(); // Stop form from submitting immediately

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This action cannot be undone!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit(); // Submit the form if confirmed
                        }
                    });
                });
            });
        });
    </script>

@endsection
