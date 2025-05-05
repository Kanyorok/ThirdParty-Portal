@extends('layouts.app')
@section('title', 'Ledger Account List')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h1>Ledger Account List</h1>
                <a href="{{ route('ledgeraccounts.create') }}" class="btn btn-primary mb-3">Create a ledger account</a>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Batch Name</th>
                            <th>Description</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                            <tr>
                                <td>67</td>
                                <td>yield</td>
                                <td>A time to celebrate</td>
                                <td>7/8/2024</td>
                                <td>Pending</td>
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