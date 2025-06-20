@extends('layouts.app')
@section('title', 'Budget Line Categories')

@section('content')
    <div class="card p-4">

        <div class="card-header bg-dark text-white mb-0">
            📂 Budget Line Categories
        </div>

        <div class="card-body">
            <p class="text-muted mb-3">
                Manage your budget line categories here. You can create, edit, and delete categories to organize your
                budget lines effectively.


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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="{{ route('budgetlinecategories.create') }}" class="btn btn-primary bg-light text-dark">
                    +Create Category
                </a>
            </div>
            @if(count($budgetLineCategories))
                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Category Code</th>
                        <th>Category Name</th>
                        <th>Description</th>
                        <th>Active</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($budgetLineCategories as  $category)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $category->CategoryCode }}</td>
                            <td>{{ $category->CategoryName }}</td>
                            <td>{{ $category->Description ?? '—' }}</td>
                            <td>
                                @if($category->IsActive)
                                    <span class="badge bg-success">Yes</span>
                                @else
                                    <span class="badge bg-danger">No</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    @else
                        <div class="alert alert-info">
                            No budget line categories found. <a href="{{ route('budgetlinecategories.create') }}">Create
                                one now</a>.
                    @endif
                </table>
        </div>
    </div>
@endsection
