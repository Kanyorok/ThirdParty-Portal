@extends('layouts.app')
@section('title', 'Committee Members Overview')
@section('content')
    <div class="container mt-4">
        <h4 class="mb-3">📄 Members with Roles ({{$tenderTitle}})</h4>
        <a href="{{ route('assignrole.create') }}" class="btn btn-primary mb-3" data-bs-toggle="modal"
           data-bs-target="#addRoleModal">
            Assign Role</a>
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-light">
                <tr>
                    <th>Member Name</th>
                    <th>Current Role</th>
                    <th>Appointment Date</th>
                    <th>Status</th>
                    {{-- <th>Actions</th> --}}
                </tr>
                </thead>
                <tbody>
                <!-- Example Row -->
                @forelse ($committeeMembers as $item)
                    <tr>
                        <td>{{ $item->employee->FirstName }} {{ $item->employee->LastName }}</td>
                        <td>{{$item->Role}} </td>
                        <td>{{$item->modifiedBy->CreatedOn->format('jS F Y')}}0</td>
                        @if($item->Response == '0')
                            <td><span class="badge bg-warning">Pending</span></td>
                        @elseif($item->Response == '1')
                            <td><span class="badge bg-success">Accepted</span></td>
                        @elseif($item->Response == '2')
                            <td><span class="badge bg-danger">Declined</span></td>
                        @endif
                        {{-- <td>
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </td> --}}
                    </tr>
                @empty

                @endforelse
                <!-- Repeat rows for other members -->
                </tbody>
            </table>
        </div>
    </div>




    <!-- Add New Committee Role Modal -->
    <div class="modal fade" id="addRoleModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content rounded-3 shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="addItemModalLabel">Appoint Committee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="{{route('tendercommittee.save')}}" method="POST">
                    @csrf
                    @method('POST')

                    <div class="modal-body">
                        <!-- Tender Selection -->
                        <div class="mb-3">
                            <label for="tenderRef" class="form-label">Tender Reference</label>
                            <input type="text" name="tenderTitle" class="form-control" id="tenderRef"
                                   value="{{$tenderTitle}}" readonly>
                            <input type="hidden" name="tenderID" value="{{$tenderID}}">
                        </div>

                        <!-- Committee Members and Role Assignment -->
                        <div class="mb-3">
                            <label class="form-label">Roles Management</label>
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead class="table-light">
                                    <tr>
                                        <th>Member Name</th>
                                        <th>Current Role</th>
                                        <th>Assign New Role</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <!-- Example Row -->
                                    @forelse ($committeeMembers as $item)
                                        <tr>
                                            <td>{{ $item->employee->FirstName }} {{ $item->employee->LastName }}</td>
                                            <input type="hidden" name="memberID[]" value="{{ $item->UserID }}">
                                            <td>{{ $item->Role }}</td>
                                            <td>
                                                <select class="form-select" name="memberRole[]">
                                                    <option disabled {{ $item->Role == null ? 'selected' : '' }}>--
                                                        Select Role --
                                                    </option>
                                                    <option
                                                        value="Chairperson" {{ $item->Role == 'Chairperson' ? 'selected' : '' }}>
                                                        Chairperson
                                                    </option>
                                                    <option
                                                        value="Technical Evaluator" {{ $item->Role == 'Technical Evaluator' ? 'selected' : '' }}>
                                                        Technical Evaluator
                                                    </option>
                                                    <option
                                                        value="Financial Evaluator" {{ $item->Role == 'Financial Evaluator' ? 'selected' : '' }}>
                                                        Financial Evaluator
                                                    </option>
                                                    <option
                                                        value="Legal Advisor" {{ $item->Role == 'Legal Advisor' ? 'selected' : '' }}>
                                                        Legal Advisor
                                                    </option>
                                                    <option
                                                        value="Observer" {{ $item->Role == 'Observer' ? 'selected' : '' }}>
                                                        Observer
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4">
                                                <i class="fas fa-users fa-2x text-muted mb-2"></i><br>
                                                No Members Yet. <a href="#" data-bs-toggle="modal"
                                                                   data-bs-target="#addRoleModal">Create a new one?</a>
                                            </td>
                                        </tr>

                                    @endforelse
                                    <!-- Repeat rows for other members -->
                                    </tbody>
                                </table>
                            </div>
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
