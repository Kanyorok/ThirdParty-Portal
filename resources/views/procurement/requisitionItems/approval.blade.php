@extends('layouts.app')
@section('title', 'Requisition Approval')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
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
                                <th>Remarks</th>
                                <th>Total Items</th>
                                <th>Total Cost</th>
                                <th>Status</th>
                                <th>Requested By</th>
                                {{-- <th>Approved By</th> --}}
                                <th>Action</th>
                            </tr>

                        </thead>
                        <tbody>
                             @forelse($details as $item)
                                <tr>
                                    <td>{{$loop->iteration }}</td>
                                    <td>{{ $item->RequisitionNo }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->CreatedOn)->format('d-m-Y') }}</td>
                                    <td>{{ $item->BranchID }}</td>
                                    <td>{{ $item->DepartmentID }}</td>
                                    <td>{{ $item->Category }}</td>
                                    <td>{{ $item->Remarks }}</td>
                                    <td>{{ $item->itemcount }}</td>
                                    <td>{{ number_format($item->ExpectedPrice, 2) }}</td>
                                    <td>{{ $item->Status }}</td>
                                    <td><a href="{{ route('requisitionItem.show',['id' => $item->Id]) }}" class="btn btn-info">View</a></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="15" class="text-center">No requisition items found.</td>
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
