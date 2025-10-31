@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        {{-- Left Panel: Vehicle Details --}}
        <div class="col-md-4">
            <div class="card h-100 p-3 shadow rounded-4">
                <h5 class="card-title fw-bold">Vehicle Details</h5>
                <hr>

                {{-- Vehicle Images --}}
                @if ($vehicle->image)
                    <img src="data:{{ $vehicle->image->MIMEType }};base64,{{ $vehicle->image->Image }}"
                         alt="Vehicle Image"
                         class="img-fluid mb-3 rounded-circle border shadow"
                         style="width: 200px; height: 200px; object-fit: cover;">
                @else
                    <img src="{{ asset('images/vehicle-placeholder.png') }}"
                         alt="No Image"
                         class="img-fluid mb-3 rounded-circle border shadow"
                         style="width: 200px; height: 200px; object-fit: cover;">
                @endif
                <hr>

                {{-- Vehicle Details --}}
                <div class="mb-2"><strong>Registration No:</strong> <span
                        class="text-muted">{{ $vehicle->RegistrationNo }}</span></div>
                <div class="mb-2"><strong>Color:</strong> <span class="text-muted">{{ $vehicle->Color }}</span></div>
                <div class="mb-2"><strong>Brand:</strong> <span
                        class="text-muted">{{ $vehicle->brand->BrandName }}</span></div>
                <div class="mb-2"><strong>Model:</strong> <span
                        class="text-muted">{{ $vehicle->model->ModelName }}</span></div>
                <div class="mb-2"><strong>Year:</strong> <span
                        class="text-muted">{{ $vehicle->YearOfManufacture }}</span></div>
                <div class="mb-2"><strong>Chassis No:</strong> <span class="text-muted">{{ $vehicle->ChassisNo }}</span>
                </div>
                <div class="mb-2"><strong>Engine No:</strong> <span class="text-muted">{{ $vehicle->EngineNo }}</span>
                </div>
                <div class="mb-2"><strong>Capacity:</strong> <span class="text-muted">{{ $vehicle->Capacity }}</span>
                </div>
                <div class="mb-2"><strong>Odometer:</strong> <span
                        class="text-muted">{{ $vehicle->OdometerReading }}</span></div>
                <div class="mb-2"><strong>Status:</strong> <span
                        class="text-muted">{{ $vehicle->status->Description }}</span></div>
                <div class="mb-2"><strong>Branch:</strong> <span
                        class="text-muted">{{ $vehicle->branch->Name ?? '-' }}</span></div>
                <div class="mb-2"><strong>Max Load (kg):</strong> <span
                        class="text-muted">{{ $vehicle->MaxLoad ?? '-' }}</span></div>
                <div class="mb-2"><strong>Max Passengers:</strong> <span
                        class="text-muted">{{ $vehicle->MaxPassengers ?? '-' }}</span></div>
                <div class="mb-2"><strong>Vehicle Availability:</strong> <span
                        class="text-muted">{{ $vehicle->vehicleStatus->Description ?? '-' }}</span></div>
                <hr>

                <div class="d-flex justify-content-between mt-auto">
                    <a href="{{ route('fleet.vehicles.index') }}" class="btn btn-secondary">⬅ Back</a>
                    <a href="{{ route('fleet.vehicles.edit', $vehicle->Id) }}" class="btn btn-warning">✏ Edit</a>
                    <form action="{{ route('fleet.vehicles.destroy', $vehicle->Id) }}"
                          method="POST"
                          class="d-inline"
                          onsubmit="return confirm('Delete this Vehicle?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger">Delete</button>
                </form>
                </div>
            </div>
        </div>

        {{-- Right Panel: Tabs --}}
        <div class="col-md-8">
            <div class="card h-100 p-3 shadow rounded-4">
                <h5 class="card-title fw-bold">Vehicle Records</h5>
                <hr>
                <ul class="nav nav-tabs mb-3" id="vehicleTabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#trips" role="tab">🗺️ Trips</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#drivers" role="tab">👤 Drivers</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#insurance" role="tab">🛡️ Insurance</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#inspection" role="tab">🔍 Inspection</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#maintenance" role="tab">🔧 Maintenance</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#repairs" role="tab">🛠️ Repairs</a></li>
                </ul>


                <div class="tab-content">
                    {{-- Trips Tab
                    <div class="tab-pane fade show active" id="trips" role="tabpanel">
                        <h5>Trips</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Trip No</th>
                                        <th>Start Location</th>
                                        <th>Destination</th>
                                        <th>Period</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($trips as $trip)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $trip->TripNo }}</td>
                                            <td>{{ $trip->StartLocation }}</td>
                                            <td>{{ $trip->EndLocation }}</td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($trip->TripStartDate)->format('d/m/Y') }}
                                                &rarr;
                                                {{ $trip->TripEndDate ? \Carbon\Carbon::parse($trip->TripEndDate)->format('d/m/Y') : '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div> --}}

                 {{-- Drivers Tab --}}
<div class="tab-pane fade" id="drivers" role="tabpanel">
    <h5>Drivers</h5>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Source</th>
                    <th>Period</th>
                </tr>
            </thead>
            <tbody>
                @forelse($driverList as $driver)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $driver['Name'] ?? '-' }}</td>
                        <td>{{ $driver['DriverType'] ?? '-' }}</td>
                        <td>{{ $driver['Source'] ?? '-' }}</td>
                        <td>{{ $driver['Period'] ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No drivers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>


                    {{-- Insurance Tab --}}
                    <div class="tab-pane fade" id="insurance" role="tabpanel">
                        <h5>Insurance</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Insurance No</th>
                                        <th>Provider</th>
                                        <th>Premium Amount</th>
                                        <th>Period</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($insuranceRecords as $record)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $record->InsuranceNo ?? '-' }}</td>
                                            <td>{{ $record->insurance->ProviderName ?? '-' }}</td>
                                            <td>{{ number_format($record->PremiumAmount, 2) }}</td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($record->CoverageStartDate)->format('d/m/Y') }}
                                                &rarr;
                                                {{ $record->CoverageEndDate ? \Carbon\Carbon::parse($record->CoverageEndDate)->format('d/m/Y') : '—' }}
                                            </td>
                                            <td>{{ $record->insuranceStatus->Description ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No insurance records found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Inspection Tab --}}
                    <div class="tab-pane fade" id="inspection" role="tabpanel">
                        <h5>Inspection</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Inspection No</th>
                                        <th>Inspection Type</th>
                                        <th>Inspection Date</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($inspections as $inspection)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $inspection->InspectionNo ?? '-' }}</td>
                                            <td>{{ $inspection->InspectionType ?? '-' }}</td>
                                            <td>{{ $inspection->InspectionDate ? \Carbon\Carbon::parse($inspection->InspectionDate)->format('d/m/Y') : '-' }}</td>
                                            <td>{{ $inspection->DueDate ? \Carbon\Carbon::parse($inspection->DueDate)->format('d/m/Y') : '-' }}</td>
                                            <td>{{ $inspection->inspectionStatus->Description ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No inspection records found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Maintenance Tab --}}
                    <div class="tab-pane fade" id="maintenance" role="tabpanel">
                        <h5>Maintenance</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Schedule ID</th>
                                        <th>Maintenance Type</th>
                                        <th>Scheduled Date</th>
                                        <th>Scheduled Mileage</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($maintenanceLogs as $log)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $log->ScheduleID }}</td>
                                            <td>{{ $log->maintenanceType->Description ?? '-' }}</td>
                                            <td>{{ $log->ScheduledDate ? \Carbon\Carbon::parse($log->ScheduledDate)->format('d/m/Y') : '-' }}</td>
                                            <td>{{ $log->ScheduledMileage ?? '-' }}</td>
                                            <td>{{ $log->maintenanceStatus->Description ?? '-' }}

                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No maintenance records found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Repairs Tab --}}
                    <div class="tab-pane fade" id="repairs" role="tabpanel">
                        <h5>Repairs</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Repair Type</th>
                                        <th>Repair Date</th>
                                        <th>Vendor</th>
                                        <th>Cost</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($repairLogs as $repair)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $repair->repairType->Description ?? '-' }}</td>
                                            <td>{{ $repair->RepairDate ? \Carbon\Carbon::parse($repair->RepairDate)->format('d/m/Y') : '-' }}</td>
                                            <td>{{ $repair->Vendor ?? '-' }}</td>
                                            <td>{{ number_format($repair->Cost, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No repair records found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div> {{-- tab-content --}}
            </div>
        </div>
    </div>
</div>
@endsection
