@extends('layouts.app')
@section('title', 'Evaluation Dashboard')
@section('content')
<div class="container mt-4">
    <h4 class="mb-3">🎯 Evaluation Dashboard – My Assignments</h4>

    <!-- Task Table -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Tender</th>
                    <th>Supplier</th>
                    {{-- <th>Role</th> --}}
                    {{-- <th>Status</th> --}}
                    {{-- <th>Assigned On</th> --}}
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- Sample task -->
                @forelse ($data as $item)
                <tr>
                    <td>{{$loop->index+1}}</td>
                    <td>{{$item['tender']}}</td>
                    <td>{{$item['supplier']}}</td>
                    {{-- <td>{{$item['Role']}}</td> --}}
                    {{-- <td><span class="badge bg-warning">Pending</span></td> --}}
                    {{-- <td>2025-05-10</td> --}}
                    <td>
                        <a href="{{ route('bidevaluation.index',['sID'=>$item['supplierID'],'tenderId'=>$item['tenderID']]) }}" class="btn btn-sm btn-outline-primary">Evaluate</a>
                    </td>
                </tr>
                @empty

                @endforelse
                <!-- Additional tasks -->
            </tbody>
        </table>
    </div>
</div>
@endsection