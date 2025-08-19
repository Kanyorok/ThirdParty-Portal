@extends('layouts.app')
@section('title', 'Legal Search Requests')

@section('content')
<div class="card p-2 shadow rounded-4">

    <div class="card-header bg-light px-3 py-2 d-flex justify-content-between align-items-centre mb-1">
        <h4 class="text-info mb-0"><i class="fas fa-clipboard"></i> Legal Search Requests</h4>
        <a href="{{ route('legal.search_requests.create') }}" class="btn btn-info"><i class="fas fa-plus me-1"></i> New Search Request</a>
    </div>
    <div class="card-body">
        <p class="text-muted">Manage and track official search requests for legal entities, ensuring timely processing and accurate record-keeping.</p>
        <table class="table table-hover table-sm align-middle text-centre"
               style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
            <thead>
                <tr>
                    <th>Request Type</th>
                    <th>Entity Name</th>
                    <th>Requested By</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @if($requests->count())
                    @foreach($requests as $request)
                        <tr>
                            <td>{{ $request->RequestType }}</td>
                            <td>{{ $request->EntityName }}</td>
                            <td>{{ $request->RequestedBy }}</td>
                            <td>{{ \Carbon\Carbon::parse($request->RequestedOn)->format('d-m-Y') }}</td>
                            <td>
                                @if($request->Status == 'Pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @elseif($request->Status == 'Approved')
                                    <span class="badge bg-success">Approved</span>
                                @elseif($request->Status == 'Rejected')
                                    <span class="badge bg-danger">Rejected</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('legal.search_requests.show', $request->Id) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('legal.search_requests.edit', $request->Id) }}" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a> 
                                <button type="button"
                                        class="btn btn-sm btn-danger custom-delete-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#customDeleteConfirmModal"
                                        data-name="{{$request->RequestType}}-{{$request->EntityName}}"    {{-- Pass item name--}}
                                        data-route="{{ route('legal.search_requests.destroy', $request->Id) }}"> 
                                    <i  class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="6" class="text-center text-muted">No search requests found.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
 @include('components.modals.delete-confirm')
@endsection
