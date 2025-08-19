@extends('layouts.app')
@section('title', 'Legal Obligations')

@section('content')
<div class="card p-4 shadow rounded-4">
    <div class="card-header bg-light px-3 py-2 d-flex justify-content-between mb-1">
        <h4 class="text-info mb-0"><i class="fas fa-clipboard"></i> Legal Obligations</h4>
        <div>
            <a href="{{ route('legal.obligations.create') }}" class="btn btn-info"><i class="fas fa-plus me-1"></i> New Obligation</a>
            {{-- <a href="{{ route('legal.obligations.calendar') }}" class="btn btn-outline-secondary">📆 Calendar View</a> --}}
        </div>
    </div>
    <div class="card-body">
        <p class="text-muted">Maintain and track all legal obligations to ensure compliance with regulatory, contractual, and organizational requirements.</p>
        <table class="table table-hover table-sm align-middle text-centre"
            style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
            <thead>
                <tr>
                    <th>Obligation</th>
                    <th>Source</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @if($obligations->count())
                @foreach($obligations as $obligation)
                    <tr>
                        <td>{{ $obligation->Title }}</td>
                        <td>{{ $obligation->SourceType }}</td>
                        <td>{{ \Carbon\Carbon::parse($obligation->DueDate)->format('d-m-Y') }}</td>
                        <td>
                            @if($obligation->Status == 'Pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @elseif($obligation->Status == 'Completed')
                                <span class="badge bg-success">Completed</span>
                            @else
                                <span class="badge bg-danger">Overdue</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('legal.obligations.show', $obligation->Id) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                            <button 
                                type="button" 
                                class="btn btn-sm btn-primary" 
                                data-bs-toggle="modal" 
                                data-bs-target="#EditObligationsModal-{{$obligation->Id}}">
                                <i class="fas fa-edit"></i>
                            </button>
                            {{-- <a href="{{ route('legal.obligations.edit', $obligation->Id) }}" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a> --}}
                            <button type="button"
                                class="btn btn-sm btn-danger custom-delete-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#customDeleteConfirmModal"
                                data-name="{{$obligation->Title}}"   
                                data-route="{{ route('legal.obligations.destroy', $obligation->Id) }}">
                                <i  class="fas fa-trash-alt"></i>
                            </button>
                            {{-- <a href="{{ route('legal.obligations.assignments.index', $obligation->ID) }}" class="btn btn-sm btn-info">👤 Assign</a> --}}
                        </td>
                    </tr>
                @endforeach
                @else
                    <tr>
                        <td colspan="6" class="p-0">
                            <div class="text-centre p-4 border rounded-3 bg-light">
                                <p class="mb-3 text-muted fs-5">
                                    <i class="fas fa-info-circle me-2 text-info"></i>
                                    <i>No obligations found.</i>
                                </p>
                                <a href="{{ route('legal.obligations.create') }}" class="btn btn-info px-4 py-2">
                                    <i class="fas fa-plus-circle me-2"></i> New Obligation
                                </a>
                            </div>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    @foreach ($obligations as $item)
        {{--Edit Modal--}}
        <form id="editObligationsForm" method="POST" action="{{route('legal.obligations.update',$item->Id)}}">
            @csrf
            @method('PATCH')
            <div class="modal fade" id="EditObligationsModal-{{$item->Id}}" tabindex="-1" aria-hidden="true" aria-labelledby="EditObligationsModalLabel">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-light">
                            <h5 class="modal-title text-info" id="EditObligationsModalLabel"><i class="fas fa-edit"></i> Obligation</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input name="Id" type="hidden">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="ObligationTitle" class="form-label">Obligation Title</label>
                                    <input type="text" name="Title" class="form-control" value="{{ old('ObligationTitle', $item->Title) }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="SourceType" class="form-label">Source Type</label>
                                    <select name="SourceType" class="form-select">
                                        <option value="{{ old('SourceType', $obligation->SourceType)}}">{{ old('SourceType', $item->SourceType)}}</option>
                                            @foreach($details as $detail)
                                                <option value="{{ $detail->Value }}">{{ $detail->Value }}</option>
                                            @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="DueDate" class="form-label">Due Date</label>
                                    <input type="date" name="DueDate" class="form-control" value="{{ old('DueDate', \Carbon\Carbon::parse($item->DueDate)->format('Y-m-d')) }}">
                                </div>

                                <div class="col-md-6">
                                    <label for="Status" class="form-label">Status</label>
                                    <select name="Status" class="form-select" required>
                                        <option value="Pending" {{ old('Status', $item->Status) == 'Pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="Completed" {{ old('Status', $item->Status) == 'Completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="Overdue" {{ old('Status', $item->Status) == 'Overdue' ? 'selected' : '' }}>Overdue</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="Description" class="form-label">Description</label>
                                <textarea name="Description" class="form-control" required>{{ old('Description', $item->Description) }}</textarea>  
                            </div>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><i class="fas fa-window-close"></i> Close</button>
                            <button type="button" class="btn btn-outline-info" onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Editing...'; this.form.submit();}"><i class="fas fa-edit"></i> Edit</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @endforeach

</div>

@include('components.modals.delete-confirm')
@endsection
