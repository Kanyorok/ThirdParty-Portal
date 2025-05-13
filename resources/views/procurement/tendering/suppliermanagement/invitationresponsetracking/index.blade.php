@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
<a href="{{ route('tenderresponse.create') }}" class="btn btn-success">➕ Add Response</a>
    <h4>Invitation Response Tracker</h4>
    <div class="table-responsive">
        <table class="table table-bordered table-striped mt-3">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Tender Ref</th>
                    <th>Supplier</th>
                    <th>Sent On</th>
                    <th>Status</th>
                    <th>Responded On</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>TND/PROC/2025/001</td>
                    <td>Tech Supplies Ltd</td>
                    <td>2025-05-09</td>
                    <td><span class="badge bg-success">Accepted</span></td>
                    <td>2025-05-10</td>
                    <td>Ready to submit bid</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>TND/PROC/2025/001</td>
                    <td>Nova Systems</td>
                    <td>2025-05-09</td>
                    <td><span class="badge bg-danger">Declined</span></td>
                    <td>2025-05-10</td>
                    <td>Currently overbooked</td>
                </tr>
                <!-- Additional rows -->
            </tbody>
        </table>
    </div>
</div>
@endsection