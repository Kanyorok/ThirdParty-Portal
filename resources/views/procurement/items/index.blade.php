@extends('layouts.app')
@section('title','Items Catalogue')

@section('content')
<div class="container">
    <h1>Items Catalogue</h1>

    <a href="{{ route('items.create') }}" class="btn btn-primary mb-3">Add Item</a>

    <form method="GET" action="{{ route('items.index') }}" class="row mb-4">
        <div class="col-md-4">
            <select name="category_id" class="form-control">
                <option value="">-- Filter by Category --</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->Name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <select name="type" class="form-control">
                <option value="">-- Filter by Type --</option>
                <option value="goods" {{ request('type') == 'goods' ? 'selected' : '' }}>Goods</option>
                <option value="services" {{ request('type') == 'services' ? 'selected' : '' }}>Services</option>
            </select>
        </div>

        <div class="col-md-2">
            <button class="btn btn-primary" type="submit">Apply Filter</button>
        </div>

        @if(request()->has('category_id') || request()->has('type'))
            <div class="col-md-2">
                <a href="{{ route('items.download', array_merge(request()->query(), ['format' => 'xlsx'])) }}" class="btn btn-success">Download XLSX</a>
            </div>
        @endif
    </form>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Category</th>
                <th>Unit Price</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    <td>{{ $item->Name }}</td>
                    <td>{{ ucfirst($item->Type) }}</td>
                    <td>{{ optional($item->category)->Name }}</td>
                    <td>Ksh. {{ $item->UnitPrice }}</td>
                    <td>
                        <a href="{{ route('items.edit', $item) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('items.destroy', $item) }}" method="POST" style="display:inline-block">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center">No items found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
