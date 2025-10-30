@extends('layouts.app')
@section('title', 'Vehicle Inspections')

@section('content')
<div class="card shadow p-4 rounded-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">🚗 Vehicle Inspections</h4>
        <a href="{{ route('fleet.vehicle_inspection.create') }}" class="btn btn-primary">
            ➕ Log Pre-Trip Inspection
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Inspection ID</th>
                    <th>Inspection Date</th>
                    <th>Inspection Type</th>
                    <th>Vehicle</th>
                    <th>Driver</th>
                    <th>Mileage</th>
                    <th>Fuel</th>
                    <th>Engine Oil</th>
                    <th>Coolant</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inspections as $inspection)
                    <tr>
                        <td>{{ $inspection->InspectionID }}</td>
                        <td>{{ \Carbon\Carbon::parse($inspection->InspectionDate)->format('d/m/Y') }}</td>
                        <td>{{ $inspection->inspectionType->Description ?? 'N/A' }}</td>
                        <td>{{ $inspection->vehicle?->RegistrationNo }}</td>
                        <td>{{ $inspection->driver?->FullName }}</td>
                        <td>{{ $inspection->Mileage }} Km/h</td>
                        <td>{{ $inspection->fuel->Description ?? 'N/A' }}</td>
                        <td>{{ $inspection->engineOil->Description ?? 'N/A' }} </td>
                        <td>{{ $inspection->coolant->Description ?? 'N/A' }} </td>
                        <td>
                            <a href="{{ route('fleet.vehicle_inspection.show', $inspection->Id) }}"
                               class="btn btn-sm btn-info">
                                👁 Details
                            </a>
                            <a href="{{ route('fleet.vehicle_inspection.posttrip.create', $inspection->Id) }}"
                               class="btn btn-sm btn-warning">
                                + Add Post Trip
                            </a>
                        </td>
                    </tr>

                    <!-- Accordion row -->
                    <tr>
                        <td colspan="10">
                            <div class="accordion" id="accordion{{ $inspection->Id }}">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#collapse{{ $inspection->Id }}">
                                            Post Trip Inspections
                                        </button>
                                    </h2>
                                    <div id="collapse{{ $inspection->Id }}" class="accordion-collapse collapse">
                                        <div class="accordion-body">
                                            @forelse($inspection->postTrips as $postTrip)
                                                <div class="table-responsive mb-3">
                                                    <table class="table table-sm table-bordered align-middle">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>Date</th>
                                                                <th>Mileage</th>
                                                                <th>Fuel</th>
                                                                <th>Engine Oil</th>
                                                                <th>Coolant</th>
                                                                <th>Reflector</th>
                                                                <th>Fire Extinguisher</th>
                                                                <th>First Aid Kit</th>
                                                                <th>Spare Tyre</th>
                                                                <th>Spanner</th>
                                                                <th>Jack</th>
                                                                <th>4X Floor Mats</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>{{ \Carbon\Carbon::parse($inspection->InspectionDate)->format('d/m/Y') }}</td>
                                                                <td>{{ $postTrip->Mileage }} Km/h</td>
                                                                <td>{{ $postTrip->Fuel }} Ltr</td>
                                                                <td>{{ $postTrip->EngineOil }} Ltr</td>
                                                                <td>{{ $postTrip->Coolant }} Ltr</td>
                                                                <td>{{ $postTrip->Reflector ? '✔️' : '❌' }}</td>
                                                                <td>{{ $postTrip->FireExtinguisher ? '✔️' : '❌' }}</td>
                                                                <td>{{ $postTrip->FirstAidKit ? '✔️' : '❌' }}</td>
                                                                <td>{{ $postTrip->SpareTyre ? '✔️' : '❌' }}</td>
                                                                <td>{{ $postTrip->Spanner ? '✔️' : '❌' }}</td>
                                                                <td>{{ $postTrip->Jack ? '✔️' : '❌' }}</td>
                                                                <td>{{ $postTrip->XFloorMats ? '✔️' : '❌' }}</td>

                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @empty
                                                <p>No post-trip inspections yet.</p>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center">No inspections found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
