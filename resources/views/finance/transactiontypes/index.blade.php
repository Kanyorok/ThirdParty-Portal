@extends('layouts.app')
@section('title', 'Maintain Transaction Types')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h1>Transaction Types</h1>
                <a href="{{ route('transactiontypes.create') }}" class="btn btn-primary mb-3">Add/Edit a transaction type</a>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Transaction Code</th>
                            <th>Transaction Name</th>
                            <th>Description</th>
                            <th>Module</th>
                            <!-- <th>Category</th> -->
                            <!-- <th>Actions</th> -->
                        </tr>
                    </thead>
                    <tbody>
                        
                            <tr>
                                <td>67</td>
                                <td>yield</td>
                                <td>A time to celebrate</td>
                                <td>7/8/2024</td>
                                <!-- <td><a href="{{ route('transactiontypes.create') }}" class="btn btn-primary mb-3">Pending</a></td> -->
                                <!-- <td>
                                    
                                    <form action="#" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </td> -->
                            </tr>
                      
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @endsection