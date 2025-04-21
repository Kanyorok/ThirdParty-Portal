@extends('layouts.app')
@section('title', 'Tender Details')
@section('content')
<div class="container">
    <h2>{{ $period->Title }}</h2>
    <p><strong>Start Date:</strong> {{ $period->StartDate }}</p>
    <p><strong>Procurement Mode:</strong> {{ $period->EndDate }}</p>

    <hr>

    <h4>📅 Linked Suppliers</h4>

    <div class="mb-4">

        @if($period->Suppliers->isEmpty())
        <p class="text-gray-500">No suppliers assigned to this period.</p>
        @else
        <div class="flex flex-wrap gap-2 mt-2">
            @foreach($period->Suppliers as $supplier)
            <span class="bg-blue-100 text-blue-800 text-sm px-3 py-1 rounded-full">
                {{ $supplier->SupplierName }}
            </span>
            @endforeach
        </div>
        @endif
    </div>

</div>
@endsection