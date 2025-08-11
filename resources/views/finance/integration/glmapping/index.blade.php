@extends('layouts.app')
@section('title', 'GL Mappings')

@section('content')
<div class="card shadow p-2 rounded-4">

{{--    <div class="d-flex justify-content-between align-items-center mb-3">--}}
{{--     <h4 class="mb-4">📄 GL Mapping List</h4>--}}
{{--   <a href="{{ route('glpostingmap.create') }}" class="btn btn-success btn-sm">+ New Mapping</a>--}}

{{--    </div>--}}
    <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-info"><i class="fas fa-map-pin"></i> Mapping List</h5>
        <a href="{{ route('glpostingmap.create') }}" class="btn btn-info btn-sm p-2">
            <i class="fas fa-plus me-1"></i> New Mapping
        </a>
    </div>
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

    <table class="table table-hover table-sm align-middle table-striped1 text-center"
           style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;"
    >
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
          <tr>
              <td colspan="8" class="p-0">
                  <div class="text-center p-4 border rounded-3 bg-light">
                      <p class="mb-3 text-muted fs-5">
                          <i class="fas fa-info-circle me-2 text-info"></i>
                          <i>No mappings found.</i>
                      </p>
                      <a href="{{ route('glpostingmap.create') }}" class="btn btn-info px-4 py-2">
                          <i class="fas fa-plus-circle me-2"></i> Add Mapping
                      </a>
                  </div>
              </td>
          </tr>
      @endif
    </tbody>
  </table>
</div>
@endsection
