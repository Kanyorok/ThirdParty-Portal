@extends('layouts.app')
@section('title', 'Journal Batch List')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h1>Driver Management</h1>
                <a href="{{ route('drivermanagement.create') }}" class="btn btn-primary mb-3">Add Driver</a>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Driver Name</th>
                            <th>Description</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>
                                    
                                    <form action="#" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                      
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @endsection