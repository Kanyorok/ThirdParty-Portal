@extends('layouts.app')
@section('title', 'Tax GL Mappings')

@section('content')
<div class="container mt-4">
    <h4 class="mb-4">📄 Tax GL Account Mappings</h4>

    <a href="{{ route('taxglmapping.create') }}" class="btn btn-primary mb-3">➕ Add New Mapping</a>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Tax Rule</th>
                <th>Tax Type</th>
                <th>GL Account</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>VAT 16%</td>
                <td>Input</td>
                <td>2100 - VAT Payable</td>
                <td>For AP purchases</td>
            </tr>
            <tr>
                <td>2</td>
                <td>VAT 16%</td>
                <td>Output</td>
                <td>3100 - VAT Receivable</td>
                <td>For AR sales</td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
