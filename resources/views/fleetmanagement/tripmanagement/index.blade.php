@extends('layouts.app')
@section('title', 'Trip Management')
@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2>Trip Management</h2>
            <a href="{{ route('tripmanagement.create') }}" class="btn btn-primary mb-3">Add Trip Entry</a>

            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Trip ID</th>
                        <th>Vehicle</th>
                        <th>Driver</th>
                        <th>Origin</th>
                        <th>Destination</th>
                        <th>Departure</th>
                        <th>Arrival</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>TRIP001</td>
                        <td>Toyota Hilux (KDA 123A)</td>
                        <td>John Doe</td>
                        <td>Nairobi</td>
                        <td>Mombasa</td>
                        <td>2025-05-01</td>
                        <td>2025-05-02</td>
                        <td>Completed</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>TRIP002</td>
                        <td>Isuzu NQR (KCF 987B)</td>
                        <td>Jane Mwangi</td>
                        <td>Kisumu</td>
                        <td>Kericho</td>
                        <td>2025-05-03</td>
                        <td>2025-05-03</td>
                        <td>In Progress</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>TRIP003</td>
                        <td>Land Rover (KCP 234D)</td>
                        <td>Peter Otieno</td>
                        <td>Nakuru</td>
                        <td>Naivasha</td>
                        <td>2025-05-04</td>
                        <td>2025-05-04</td>
                        <td>Cancelled</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection