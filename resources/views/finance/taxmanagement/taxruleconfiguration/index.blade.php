@extends('layouts.app')
@section('title', 'Tax Rules Management')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>📄 Tax Rules</h4>
    
        <a href="{{ route('taxruleconfig.create') }}" class="btn btn-primary">➕ Add Tax Rule</a>
        
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Tax Type</th>
                        <th>Jurisdiction</th>
                        <th>Rate (%)</th>
                        <th>Applies To</th>
                        <th>Threshold</th>
                        <th>Effective From</th>
                        <th>Effective To</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>VAT</td>
                        <td>Kenya</td>
                        <td>16.00</td>
                        <td>Sales</td>
                        <td>10,000</td>
                        <td>2025-01-01</td>
                        <td>2025-12-31</td>
                        <td><span class="badge bg-success">Active</span></td>
                        <td>
                            <a href="/finance/taxrules/1/edit" class="btn btn-sm btn-warning">✏️ Edit</a>
                            <button class="btn btn-sm btn-danger">🗑 Deactivate</button>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>WHT</td>
                        <td>Uganda</td>
                        <td>6.00</td>
                        <td>Purchases</td>
                        <td>5,000</td>
                        <td>2025-03-01</td>
                        <td>2025-12-31</td>
                        <td><span class="badge bg-secondary">Inactive</span></td>
                        <td>
                            <a href="/finance/taxrules/2/edit" class="btn btn-sm btn-warning">✏️ Edit</a>
                            <button class="btn btn-sm btn-success">✅ Activate</button>
                        </td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>GST</td>
                        <td>Tanzania</td>
                        <td>18.00</td>
                        <td>Both</td>
                        <td>15,000</td>
                        <td>2025-05-01</td>
                        <td>2025-12-31</td>
                        <td><span class="badge bg-success">Active</span></td>
                        <td>
                            <a href="/finance/taxrules/3/edit" class="btn btn-sm btn-warning">✏️ Edit</a>
                            <button class="btn btn-sm btn-danger">🗑 Deactivate</button>
                        </td>
                    </tr>
                    <!-- More rows as needed -->
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
