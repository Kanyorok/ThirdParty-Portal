@extends('layouts.app')
@section('title', 'insurancetypemanagement')
@section('content')
<div class="container">
        <div class="top-link">
            <a href="{{ route('providermanagement.create') }}">← Back to Home</a>
        </div>

        <h2>Select Your Insurance Type</h2>
        <form method="post" action="">
            <div class="form-group">
                <label for="insurance_type">Insurance Type:</label>
                <select name="insurance_type" id="insurance_type" required>
                    <option value="">-- Please choose --</option>
                    <option value="Health">Health Insurance</option>
                    <option value="Auto">Auto Insurance</option>
                    <option value="Home">Home Insurance</option>
                    <option value="Life">Life Insurance</option>
                </select>
            </div>
            <div class="form-group">
                <button type="submit">Submit</button>
            </div>
@endsection