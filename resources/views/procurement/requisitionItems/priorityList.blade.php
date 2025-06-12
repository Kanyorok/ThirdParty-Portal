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
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total Price</th>
                            <th>Urgency</th>
                            <th>NeededBy</th>
                            <th>Approval Status</th>
                            <th>Requested By</th>
                            {{-- <th>Approved By</th> --}}
{{--                            <th>Action</th>--}}
                        </tr>

                        </thead>
                        <tbody>
                        @forelse($details as $item)
                            <tr>
                                <td>{{$loop->iteration }}</td>
                                <td>{{ $item->RequisitionNo }}</td>
                                <td>{{ ($item->CreatedOn) }}</td>
                                <td>{{ $item->BranchID }}</td>
                                <td>{{ $item->DepartmentID }}</td>
                                <td>{{ $item->Item }}</td>
                                <td>{{ $item->Quantity }}</td>
                                <td>{{ number_format($item->UnitPrice, 2) }}</td>
{{--                                <td>{{ $item->UnitPrice }}</td>--}}
                                <td>{{ number_format($item->ExpectedPrice, 2) }}</td>
                                <td>{{ $item->Urgency }}</td>
                                <td>{{ $item->NeededBy }}</td>
                                <td>{{ $item->Status }}</td>
                                <td>{{ $item->UserName }}</td>
{{--                                <td><a href="{{ route('requisitionItem.show',['id' => $item->Id]) }}" class="btn btn-info">View</a></td>--}}
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
