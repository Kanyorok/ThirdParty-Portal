@extends('layouts.app')
@section('title', 'Tender Committees')
@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Tender Committees</h4>
        <a href="{{ route('tendercommittee.create') }}" class="btn btn-sm btn-success" data-bs-toggle="modal"
           data-bs-target="#addCommitteeModal">
            + Appoint New Committee</a>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Tender Ref</th>
                    <th>Members</th>
                    <th>Appointment Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($tenderCommittees as $item)
                <tr>
                    <td>{{$loop->index+1}}</td>
                    <td>{{$item->tender->TenderNo}}</td>
                    <td>{{$item->members_count}}</td>
                    <td>{{$item->AppointmentDate->format('jS F Y')}}</td>
                    <td>
                        <a href="{{route('tendercommittee.show',$item->tender->Id)}}">
                            <button class="btn btn-sm btn-outline-info">View</button>
                        </a>
                        {{-- <a href="{{route('tender-criteria.index')}}" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addCriteriaModal">
                            Edit</a>
                        <a href="#" class="btn btn-sm btn-outline-danger">Delete</a> --}}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center py-4">
                        <i class="fas fa-users fa-2x text-muted mb-2"></i><br>
                        No Committee. <a href="#" data-bs-toggle="modal" data-bs-target="#addCommitteeModal">Create a
                            new one?</a>
                    </td>
                </tr>
            @endforelse
                <!-- More rows -->
            </tbody>
        </table>
    </div>
</div>



<!-- Add New Committee Modal -->
<div class="modal fade" id="addCommitteeModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-3 shadow">
            <div class="modal-header">
                <h5 class="modal-title" id="addItemModalLabel">Appoint Committee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{route('tendercommittee.store')}}" method="POST">
                @csrf
                @method('POST')

                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="tenderRef" class="form-label">Tender Reference</label>
                            <select class="form-select" id="tenderRef" name="tenderID" required>
                                <option selected disabled>-- Select Tender --</option>
                                @foreach ($tenders as $item)
                                    <option value="{{$item->Id}}">{{$item->TenderNo}} | {{$item->Title}}</option>
                                @endforeach
                            </select>
                            @error('tenderID')
                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="appointmentDate" class="form-label">Appointment Date</label>
                            <input type="date" class="form-control" name="appointmentDate" id="appointmentDate">
                        </div>
                        @error('appointmentDate')
                        <div class="alert alert-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="committeeMembers" class="form-label">Select Committee Members</label>
                        <select class="form-select" id="committeeMembers" multiple required name="committeeMembers[]">
                            <!-- Populate from system user list -->
                            @foreach ($employees as $item)
                                <option value="{{$item['Id']}}">{{$item->FirstName}} {{$item->LastName}}.
                                    – {{$item->JobTitle}}</option>
                            @endforeach
                        </select>
                        @error('committeeMembers')
                        <div class="alert alert-danger mt-2">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Hold CTRL/CMD to select multiple users.</small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button
                        type="submit"
                        class="btn btn-success"
                        onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();"
                    >
                        Appoint Committee
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
