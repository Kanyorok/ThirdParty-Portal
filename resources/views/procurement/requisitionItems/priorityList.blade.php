@extends('layouts.app')
@section('title', 'Requisition Priority List')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
    <style>
        .select2-container {
            width: 100% !important;
        }
    </style>
@endsection
@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body">
              <table id="campaignTable"
                  class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                        <thead>

                        <tr>
                            <th>#</th>
                            <th>Requisition No</th>
                            <th>Requisition Date</th>
                            <th>Branch</th>
                            <th>Department</th>
                            <th>Requested By</th>
                            <th>Total Items</th>
                            <th>Very High</th>
                            <th>High</th>
                            <th>Medium</th>
                            <th>Low</th>
                            <th>Score</th>
                            <th>Action</th>
                        </tr>

                        </thead>
                        <tbody>
                        @forelse($details as $item)
                            <tr>
                                <td>{{$loop->iteration }}</td>
                                <td>{{ $item->RequisitionNo }}</td>
                                <td>{{ $item->RequisitionDate }}</td>
                                <td>{{ $item->BranchID }}</td>
                                <td>{{ $item->DepartmentID }}</td>
                                <td>{{ $item->RequestedBy }}</td>
                                <td>{{ $item->TotalItems }}</td>
                                <td>{{ $item->VeryHighCount }}</td>
                                <td>{{ $item->HighCount }}</td>
                                <td>{{ $item->MediumCount }}</td>
                                <td>{{ $item->LowCount }}</td>
                                <td>{{ $item->Score }}</td>
                                <td><a href="{{ route('requisition.show', $item->Id) }}" class="btn btn-sm btn-primary">View</a></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="15" class="text-center">No requisitions found.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('scripts')
@endsection
