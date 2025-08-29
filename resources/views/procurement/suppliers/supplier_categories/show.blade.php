@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Supplier Category Details</h1>

    <div class="bg-white p-6">
        <div class="mb-4">
            <p class="text-lg font-bold text-gray-700">Category Name:</p>
            <p class="text-xl text-gray-900">{{ $supplierCategory->CategoryName }}</p>
        </div>

        <div class="mb-4">
            <p class="text-lg font-bold text-gray-700">Description:</p>
            <p class="text-gray-800">{{ $supplierCategory->Description ?? 'N/A' }}</p>
        </div>

        <div class="mb-4">
            <p class="text-lg font-bold text-gray-700">Status:</p>
            <span class="relative inline-block px-3 py-1 font-semibold text-{{ $supplierCategory->IsActive ? 'green' : 'red' }}-900 leading-tight">
                <span aria-hidden class="absolute inset-0 bg-{{ $supplierCategory->IsActive ? 'green' : 'red' }}-200 opacity-50"></span>
                <span class="relative">{{ $supplierCategory->IsActive ? 'Active' : 'Inactive' }}</span>
            </span>
        </div>

        <div class="flex items-center justify-between">
            <a href="{{ route('procurement.suppliers.supplier-categories.edit', $supplierCategory) }}" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 focus:outline-none">
                Edit
            </a>
            <a href="{{ route('procurement.suppliers.supplier-categories.index') }}" class="inline-block align-baseline font-bold text-sm text-gray-500 hover:text-gray-800">
                Back to List
            </a>
        </div>
    </div>
</div>
@endsection