@extends('layouts.app')
@section('title', 'Commission Rules')
@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection
@section('content')
    <div class="container mt-4">

        <a href="{{ route('commissions.rules.create') }}" class="btn btn-primary mb-3">Add Commission Rule</a>

        <p><small>The list below Consists of commission rules</small></p>

        <table id='commissionrules' class="table table-bordered">
            <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Rule Name</th>
                <th>Product</th>
                <th>Policy Type</th>
                <th>Commission Rate (%)</th>
                <th>Fixed Amount</th>
                <th>Applies To</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($rules as $rule)
                <tr>
                    <td>{{ $rule->Id ?? '-'}}</td>
                    <td>{{ $rule->RuleName ?? '-'}}</td>
                    <td>{{ $rule->product->Name ?? '-'}}</td>
                    <td>{{ $rule->policytypes->Description ?? '-'}}</td>
                    <td>{{ $rule->CommissionRate ?? '-'}}</td>
                    <td>{{ $rule->FixedAmount ?? '-'}}</td>
                    <td>{{ $rule->appliesto->Description ?? '-'}}</td>
                    <td>{{ $rule->IsActive ? '✅ Active' : '❌ Inactive' ?? '-' }}</td>
                    <td>
                        <a href="{{ route('commissions.rules.edit', $rule->Id) }}"
                           class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('commissions.rules.destroy', $rule->Id) }}" method="POST"
                              class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Are you sure you want to delete this rule?');">Delete
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
            $('#commissionrules').DataTable({
                pageLength: 10,
                ordering: true,
                searching: true,
                lengthChange: true
            });
        });
    </script>
@endsection
