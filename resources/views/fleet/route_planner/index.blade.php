@extends('layouts.app')
@section('title', 'Planned Routes')

@section('content')
<div class="card p-4 shadow rounded-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">🗺️ Route Planner</h4>
        <a href="{{ route('fleet.route_planner.create') }}" class="btn btn-primary">➕ New Route</a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Trip Name</th>
                    <th>Vehicle</th>
                    <th>Waypoints</th>
                    <th>Created On</th>
                </tr>
            </thead>
            <tbody>
                @forelse($routes as $route)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $route->TripName }}</td>
                        <td>{{ $route->vehicle->RegistrationNumber ?? '-' }}</td>
                        <td>
                            <ul class="mb-0">
                                @foreach(json_decode($route->Waypoints, true) as $point)
                                    <li>{{ $point }}</li>
                                @endforeach
                            </ul>
                        </td>
                        <td>{{ $route->CreatedOn }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">No planned routes found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
