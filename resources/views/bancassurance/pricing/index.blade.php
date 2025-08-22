@extends('layouts.app')
@section('title', 'Pricing Rules')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
<div class="container mt-4">

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

    <div class="mb-3 text-end">
        <a href="{{ route('bancassurance.pricing.create') }}" class="btn btn-primary">Add Pricing Rule</a>
    </div>

        <table id='InsurancePricingRule'class="table table-bordered table-striped">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Provider</th>
                    <th>Product</th>
                    <th>Coverage Range</th>
                    <th>Age Range</th>
                    <th>Tenure (Months)</th>
                    <th>Rate (%)</th>
                    <th>IsActive</th>
                    <th>Created</th>
                </tr>
                </thead>
                <tbody>
                @foreach($rules as $rule)
                <tr>
                    <td>{{ $rule->Id }}</td>
                    <td>{{ $rule->provider->Name}}</td>
                    <td>{{ $rule->product->Name }}</td>
                    <td>
                       {{ number_format($rule->CoverageAmountMax, 2) }} -
                        {{ number_format($rule->CoverageAmountMin, 2) }}
                    </td>
                    <td>{{ $rule->AgeMin }} - {{ $rule->AgeMax }}</td>
                    <td>{{ $rule->TenureMin }} - {{ $rule->TenureMax }}</td>
                    <td>{{ number_format($rule->PremiumRate, 2) }}%</td>
                                        <td>
                        @if($rule->IsActive)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td>
                         <a href="{{ route('bancassurance.pricing.edit', $rule->Id) }}" class="btn btn-sm btn-warning">Edit</a>
                         <form method="POST" action="{{ route('bancassurance.pricing.destroy', $rule->Id) }}" method="POST" class="d-inline">
                         @csrf
                         @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger"
                                onclick="return confirm('Are you sure you want to delete this Rule  ?');">Delete
                        </button>
                    </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        $('#InsurancePricingRule').DataTable({
            pageLength: 10,
            ordering: true,
            searching: true,
            lengthChange: true
        });
    });
</script>
@endsection
