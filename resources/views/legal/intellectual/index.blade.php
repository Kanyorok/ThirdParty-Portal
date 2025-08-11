@extends('layouts.app')
@section('title', 'IP & Trademark Registry')

@section('content')
<div class="card shadow p-4 rounded-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">🧠 IP & Trademark Registry</h4>
        <a href="{{ route('legal.intellectual.create') }}" class="btn btn-primary">➕ Register IP</a>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Type</th>
                <th>Title</th>
                <th>Owner</th>
                <th>Status</th>
                <th>Registration No.</th>
                <th>Expiry Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
        <td>Vehicle</td>
        <td>Toyota Land Cruiser</td>
        <td>James Kamau</td>
        <td>Active</td>
        <td>KCE 245G</td>
        <td>2026-03-15</td>
        <td>
            <a href="#" class="btn btn-sm btn-primary">View</a>
            <a href="#" class="btn btn-sm btn-warning">Edit</a>
            <a href="#" class="btn btn-sm btn-danger">Delete</a>
        </td>
    </tr>
    <tr>
        <td>Patent</td>
        <td>Solar Water Pump Design</td>
        <td>Mary Wanjiru</td>
        <td>Expired</td>
        <td>PT-10234</td>
        <td>2022-09-10</td>
        <td>
            <a href="#" class="btn btn-sm btn-primary">View</a>
            <a href="#" class="btn btn-sm btn-warning">Edit</a>
            <a href="#" class="btn btn-sm btn-danger">Delete</a>
        </td>
    </tr>
    <tr>
        <td>Property</td>
        <td>Residential Plot - Ruiru</td>
        <td>Samuel Otieno</td>
        <td>Active</td>
        <td>PR-55678</td>
        <td>—</td>
        <td>
            <a href="#" class="btn btn-sm btn-primary">View</a>
            <a href="#" class="btn btn-sm btn-warning">Edit</a>
            <a href="#" class="btn btn-sm btn-danger">Delete</a>
        </td>
    </tr>
    <tr>
        <td>Business License</td>
        <td>Kiosk Retail License</td>
        <td>Lucy Mwikali</td>
        <td>Pending Renewal</td>
        <td>BL-98765</td>
        <td>2025-11-30</td>
        <td>
            <a href="#" class="btn btn-sm btn-primary">View</a>
            <a href="#" class="btn btn-sm btn-warning">Edit</a>
            <a href="#" class="btn btn-sm btn-danger">Delete</a>
        </td>
    </tr>
    <tr>
        <td>Trademark</td>
        <td>Kwetu Foods Logo</td>
        <td>Peter Njoroge</td>
        <td>Active</td>
        <td>TM-22014</td>
        <td>2028-07-22</td>
        <td>
            <a href="#" class="btn btn-sm btn-primary">View</a>
            <a href="#" class="btn btn-sm btn-warning">Edit</a>
            <a href="#" class="btn btn-sm btn-danger">Delete</a>
        </td>
    </tr>
            {{-- @forelse ($records as $record)
                <tr>
                    <td>{{ $record->IPType }}</td>
                    <td>{{ $record->Title }}</td>
                    <td>{{ $record->Owner }}</td>
                    <td>{{ $record->Status }}</td>
                    <td>{{ $record->RegistrationNumber }}</td>
                    <td>{{ $record->ExpiryDate }}</td>
                    <td>
                        <a href="{{ route('legal.intellectual.show', $record->ID) }}" class="btn btn-sm btn-info">View</a>
                        <a href="{{ route('legal.intellectual.edit', $record->ID) }}" class="btn btn-sm btn-warning">Edit</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7">No records found.</td></tr>
            @endforelse --}}
        </tbody>
    </table>
</div>
@endsection
