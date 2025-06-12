@extends('layouts.app')

@section('title', 'Plan Consolidation')

@section('content')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <div class="card p-4 shadow rounded-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-4">📂 Submit Plans for Approval</h4>
        </div>
        <p>The information below is awaiting submission for approval</p>

        <table id="submitplan" class="table table-bordered table-striped align-middle">
            <thead>
            <tr>
                <th>#</th>
                <th>Plan Ref</th>
                <th>Title</th>
                <th>Fiscal Year</th>
                <th>Status</th>
                <th>Created By</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($draftedplans as $index => $draftedplan)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $draftedplan->ReferenceNumber }}</td>
                    <td>{{ $draftedplan->Title }}</td>
                    <td>{{ $draftedplan->FiscalYear }}</td>
                    <td>{{ $draftedplan->Status->label() }}</td>
                    <td>{{ $draftedplan->creator->Name }}</td>
                    <td>
                        <a href="{{ route('Procurement-Plan-Submission.create', ['PlanId' => $draftedplan->PlanID]) }}"
                           class="btn btn-sm btn-primary">
                            View To Submit
                        </a>
                    </td>
                </tr>
            @empty
            @endforelse
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#submitplan').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true
            });
        });
    </script>
@endsection
