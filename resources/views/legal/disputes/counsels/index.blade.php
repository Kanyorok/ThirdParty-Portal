@extends('layouts.app')
@section('title', 'Counsel Assignment – ' . $case->CaseTitle)

@section('content')
    <div class="card p-1 shadow rounded-4 border-0 mb-0">
        <div class="card-header bg-light px-3 py-2 d-flex justify-content-between align-items-center mb-2">
            <h4 class="text-info mb-0">
                <i class="fas fa-user-tie"></i> Legal Counsels – {{ $case->CaseTitle }}
            </h4>
            <a href="{{ route('legal.disputes.counsels.create', $case->Id) }}" class="btn btn-info">
                <i class="fas fa-plus me-1"></i> Assign New Counsel
            </a>
        </div>

        <div class="card-body">
            <p class="text-muted">List of legal counsels assigned to this case.</p>

            <table class="table table-hover table-sm align-middle text-centre"
                   style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Firm</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Assigned On</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($counsels as $counsel)
                    <tr>
                        <td>{{ $counsel->CounselName }}</td>
                        <td>{{ $counsel->FirmName }}</td>
                        <td>{{ $counsel->Email }}</td>
                        <td>{{ $counsel->Phone }}</td>
                        <td>{{ $counsel->Role }}</td>
                        <td>{{ \Carbon\Carbon::parse($counsel->AssignedOn)->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('legal.disputes.counsels.show', [$case->Id, $counsel->Id]) }}"
                               class="btn btn-sm btn-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('legal.disputes.counsels.edit', [$case->Id, $counsel->Id]) }}"
                               class="btn btn-sm btn-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            {{-- Future delete option --}}
                            {{--
                            <button type="button"
                                class="btn btn-sm btn-danger custom-delete-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#customDeleteConfirmModal"
                                data-name="{{$counsel->CounselName}}"
                                data-route="{{ route('legal.disputes.counsels.destroy', [$case->Id, $counsel->Id]) }}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                            --}}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-0">
                            <div class="text-centre p-4 border rounded-3 bg-light">
                                <p class="mb-3 text-muted fs-5">
                                    <i class="fas fa-info-circle me-2 text-info"></i>
                                    <i>No counsels assigned yet.</i>
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
