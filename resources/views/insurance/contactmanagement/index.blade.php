@extends('layouts.app')
@section('title', 'contactmanagement')
@section('content')
<div class="form-container">
        <div class="top-link">
            <a href="{{ route('contactmanagement.create') }}">← Back to Home</a>
        </div>
        <h2>Contact Management Form</h2>
        <form action="submit.php" method="post">
            <div class="form-group">
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" required />
            </div>
            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" required />
            </div>
            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <input type="tel" id="phone" name="phone" />
            </div>
            <div class="form-group">
                <label for="notes">Notes:</label>
                <textarea id="notes" name="notes" rows="4"></textarea>
            </div>
            <div class="form-group">
                <button type="submit">Save Contact</button>
            </div>
@endsection