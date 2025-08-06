@extends('layouts.app')
@section('title', 'GL Mappings')
 
@section('content')
<div class="card shadow p-4 rounded-4">
 
    <div class="d-flex justify-content-between align-items-center mb-3">
     <h4 class="mb-4">📄 GL Mapping List</h4>
   <a href="{{ route('glpostingmap.create') }}" class="btn btn-success btn-sm">+ New Mapping</a>
   
    </div>
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
 
  <table class="table table-bordered table-hover">
    <thead>
      <tr>
        <th>#</th>
        <th>Module</th>
        <th>Transaction Type</th>
        <th>Debit GL</th>
        <th>Credit GL</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      @if($mappings->count())
      @foreach($mappings as $map)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td>{{ $map->modules->Name }}</td>
          <td>{{ $map->transactions->Name }}</td>
          <td>{{ $map->debitAccount->GLName }}</td>
          <td>{{ $map->creditAccount->GLName }}</td>
          <td>
            <button onclick="window.print()" class="btn btn-primary btn-sm no-print">Print</button>
            <a href="#" class="btn btn-sm btn-warning">Edit</a>
            <button type="button"
                class="btn btn-sm btn-danger custom-delete-btn"
                {{-- data-bs-toggle="modal"
                data-bs-target="#customDeleteConfirmModal"
                data-name="{{$item->taxType->TaxTypeName}}"    {{-- Pass item name --}}
                {{-- data-route="{{ route('taxruleconfig.destroy', $item->Id) }}"> Pass delete route --}} >
                Delete
            </button>
          </td>
        </tr>
      @endforeach
      @else
        <tr><td colspan="6">No mappings found.</td></tr>
      @endif
    </tbody>
  </table>
</div>
@endsection
 