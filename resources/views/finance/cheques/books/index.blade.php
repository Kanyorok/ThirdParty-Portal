@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4>Cheque Books</h4>
  <a href="{{ route('finance.chequebooks.create') }}" class="btn btn-primary">New Cheque Book</a>
</div>

@if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
@if(session('error'))   <div class="alert alert-danger">{{ session('error') }}</div> @endif

<div class="table-responsive">
<table class="table table-sm table-striped align-middle">
  <thead class="table-light">
    <tr>
      <th>#</th>
      <th>Bank</th>
      <th>Account</th>
      <th>Book</th>
      <th>Range</th>
      <th>Next</th>
      <th>Issued/Total</th>
      <th>Active</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    @forelse($rows as $b)
      <tr>
        <td>{{ $b->ChequeBookID }}</td>
        <td>{{ optional($b->bankAccount->bank)->BankName }}</td>
        <td>{{ $b->bankAccount?->AccountNumber }}</td>
        <td>{{ $b->BookName ?? '-' }} <small class="text-muted">{{ $b->Prefix }}..{{ $b->Suffix }}</small></td>
        <td>{{ $b->StartNumber }} - {{ $b->EndNumber }}</td>
        <td>{{ $b->NextLeafNumber }}</td>
        <td>{{ $b->LeavesIssued }}/{{ $b->LeavesTotal }}</td>
        <td>
          <span class="badge bg-{{ $b->IsActive ? 'success':'secondary' }}">{{ $b->IsActive ? 'Yes':'No' }}</span>
        </td>
        <td class="text-end">
          <a href="{{ route('finance.chequebooks.edit',$b->ChequeBookID) }}" class="btn btn-sm btn-outline-primary">Edit</a>
          <form action="{{ route('finance.chequebooks.destroy',$b->ChequeBookID) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this cheque book?');">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-danger">Delete</button>
          </form>
          {{-- in resources/views/finance/cheques/books/index.blade.php, actions column --}}
            <a href="{{ route('finance.chequebooks.show',$b->ChequeBookID) }}" class="btn btn-sm btn-outline-secondary">
            Manage Leaves
            </a>
        </td>
      </tr>
    @empty
      <tr><td colspan="9" class="text-center text-muted">No cheque books yet.</td></tr>
    @endforelse
  </tbody>
</table>
</div>

{{ $rows->links() }}
@endsection
