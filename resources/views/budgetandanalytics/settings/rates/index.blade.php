@extends('layouts.app')
@section('title', 'Budget Rate Types')

@section('content')

<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5>📊 Budget Rate Types</h5>


        <p class="text-muted mb-1 fs-5">Manage budget rate types used in financial calculations.</p>

        {{-- <a href="{{route('rates.create')}}" class="btn btn-primary">➕ Add New Rate</a> --}}
    </div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <table class="table table-bordered table-hover align-middle table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Rate Code</th>
                <th>Rate Name</th>
                <th>Description</th>
                {{-- <th>Default?</th> --}}

                {{-- <th>Actions</th> --}}
            </tr>
        </thead>
        <tbody>
            @forelse($rates as $index => $rate)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $rate->RateTypeCode }}</td>
                    <td>{{ $rate->RateTypeName }}</td>
                    <td>{{ $rate->Description }}</td>
                    {{-- <td>{{ $rate->IsDefault ? 'Yes' : 'No' }}</td> --}}

                    {{-- <td>
                     <div class="d-flex gap-2">
                        <a href="{{route('rates.edit', $rate->Id)}}" class="btn btn-sm btn-warning">Edit</a>
                        
                        <form action="{{route('rates.destroy', $rate->Id)}}" method="POST" style="display: inline-" onsubmit="return confirm('Are you sure you want to delete this rate?');">
                            @method('DELETE')
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                     </div>
                    </td> --}}
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No budget rates available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
