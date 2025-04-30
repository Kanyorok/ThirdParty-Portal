@extends('layouts.app')
@section('title', 'RFQ Responses')
@section('content')
<div class="container">
    <h3>RFQ Response List</h3>

    <div class="container mt-3">
        
        <a href="{{ route('rfqresponses.create') }}" class="btn btn-primary mb-2">Create RFQ Response</a>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>RFQ ID</th>
                    <th>Comments</th>
                    <th>Submission Deadline</th>
                    <th>Price</th>
                    <th>Currency</th>
                    <th>Delivery Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rfqResponses as $response)
                <tr>
                    <td>{{ $response->id }}</td>
                    <td>{{ $response->RFQId }}</td>
                    <td>{{ $response->Comments }}</td>
                    <td>{{ $response->SubmissionDeadline }}</td>
                    <td>{{ $response->Price }}</td>
                    <td>{{ $response->Currency }}</td>
                    <td>{{ $response->DeliveryDate }}</td>
                    <td>
                        <!-- Add edit and delete actions here -->
                        <!-- Example: -->
                        <!--
                        <a href="{{ route('rfqresponses.edit', $response->id) }}" class="btn btn-warning">Edit</a>
                        -->
                        <!--
                        <form action="{{ route('rfqresponses.destroy', $response->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                        -->
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

    </div>
</div>
@endsection