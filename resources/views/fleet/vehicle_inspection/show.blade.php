@extends('layouts.app')
@section('title', 'View Vehicle Inspection')

@section('content')
<div class="card shadow p-4 rounded-4 mb-4">
    <h4 class="mb-4">Pre and Post Trip Vehicle Inspection Overview</h4>

    <div class="row">
        <div class="col-md-6">
            <p><strong>Inspection ID:</strong> {{ $inspection->InspectionID }}</p>
            <p><strong>Vehicle:</strong> {{ $inspection->vehicle->RegistrationNo ?? 'N/A' }}</p>
        </div>
        <div class="col-md-6">
            <p><strong>Driver:</strong> {{ $inspection->driver->FullName ?? 'N/A' }}</p>
            <p><strong>Fuel Type:</strong> {{ $inspection->fuel->FuelName ?? 'N/A' }}</p>
        </div>
    </div>
</div>

<div class="row">
    <!-- Pre-Trip -->
    <div class="col-md-6">
        <div class="card shadow p-4 rounded-4">
            <h5 class="mb-3">🟢 Pre-Trip Details</h5>
            <div class="mb-4 border rounded p-3">
            <table class="table table-sm table-bordered">
                <tr><th>Inspection Date</th><td>{{ \Carbon\Carbon::parse($inspection->InspectionDate)->format('d/m/Y') }}</td></tr>
                <tr><th>Mileage</th><td>{{ $inspection->Mileage }} Km/h</td></tr>
                <tr><th>Fuel (Litres)</th><td>{{ $inspection->fuel->Description }} </td></tr>
                <tr><th>Engine Oil</th><td>{{ $inspection->engineOil->Description }} </td></tr>
                <tr><th>Coolant</th><td>{{ $inspection->coolant->Description }} </td></tr>
            </table>

            <h6 class="mt-3">🛡 Safety Equipment</h6>
            <ul>
                @foreach([
                    'Reflector' => 'Reflector',
                    'FireExtinguisher' => 'Fire Extinguisher',
                    'FirstAidKit' => 'First Aid Kit',
                    'SpareTyre' => 'Spare Tyre',
                    'Spanner' => 'Spanner',
                    'Jack' => 'Jack',
                    '4XFloorMats' => '4X Floor Mats',
                ] as $field => $label)
                    <li>{{ $label }}: <strong>{{ $inspection->$field ? '✅ Available' : '❌ Missing' }}</strong></li>
                @endforeach
            </ul>
            <div class="card-footer bg-light">
            <h6 class="fw-bold mb-2">📄 Documents</h6>
            @forelse($inspection->documents()->get(['t_Documents.Id', 't_Documents.DocumentId','MimeType','Name']) as $document)
                {!! (new \App\Services\DMS\DocumentService($document))->summaryList() !!}
            @empty
                <p class="text-muted mb-0">No documents uploaded.</p>
            @endforelse
        </div>
        
            <div class="mt-2">
                        <a href="{{ route('fleet.vehicle_inspection.edit', $inspection->Id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('fleet.vehicle_inspection.destroy', $inspection->Id) }}"
                              method="POST" class="d-inline"
                              onsubmit="return confirm('Delete this post-trip inspection?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </div>
                    </div>
        </div>
    </div>

   <!-- Post-Trips -->
<div class="col-md-6">
    <div class="card shadow p-4 rounded-4">
        <h5 class="mb-3">🔵 Post-Trip Details</h5>

        @forelse($inspection->postTrips as $postTrip)
            <div class="mb-4 border rounded p-3">
                <table class="table table-sm table-bordered">
                    <tr><th>Inspection Date</th><td>{{ \Carbon\Carbon::parse($postTrip->InspectionDate)->format('d/m/Y') }}</td></tr>
                    <tr><th>Mileage</th><td>{{ $postTrip->Mileage }} Km/h</td></tr>
                    <tr><th>Fuel</th><td>{{ $postTrip->Fuel }} Ltr</td></tr>
                    <tr><th>Engine Oil</th><td>{{ $postTrip->EngineOil }} Ltr</td></tr>
                    <tr><th>Coolant</th><td>{{ $postTrip->Coolant }} Ltr</td></tr>
                </table>

                <h6 class="mt-2">🛡 Safety Equipment</h6>
                <ul>
                    @foreach([
                        'Reflector' => 'Reflector',
                        'FireExtinguisher' => 'Fire Extinguisher',
                        'FirstAidKit' => 'First Aid Kit',
                        'SpareTyre' => 'Spare Tyre',
                        'Spanner' => 'Spanner',
                        'Jack' => 'Jack',
                        'XFloorMats' => '4X Floor Mats',
                    ] as $field => $label)
                    <li>{{ $label }}: <strong>{{ $inspection->$field ? '✅ Available' : '❌ Missing' }}</strong></li>
                @endforeach
                </ul>
                <div class="card-footer bg-light">
            <h6 class="fw-bold mb-2">📄 Documents</h6>
            @forelse($postTrip->documents()->get(['t_Documents.Id', 't_Documents.DocumentId','MimeType','Name']) as $document)
                {!! (new \App\Services\DMS\DocumentService($document))->summaryList() !!}
            @empty
                <p class="text-muted mb-0">No documents uploaded.</p>
            @endforelse
        </div>

                <div class="mt-2">
                    <a href="{{ route('fleet.vehicle_inspection.edit', $postTrip->Id) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('fleet.vehicle_inspection.destroy', $postTrip->Id) }}"
                          method="POST" class="d-inline"
                          onsubmit="return confirm('Delete this post-trip inspection?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <p>No post-trip inspections recorded.</p>
            <a href="{{ route('fleet.vehicle_inspection.create', $inspection->Id) }}"
               class="btn btn-primary mt-2">
               ➕ Add Post Trip Inspection
            </a>
        @endforelse
    </div>
</div>


<div class="mt-4">
    <a href="{{ route('fleet.vehicle_inspection.index') }}" class="btn btn-secondary">Back</a>
</div>
@endsection
@section('scripts')
 @include('snippets.actions.preview-files')
 @endsection

