@extends('layouts.app')
@section('title', 'Supplier Evaluation Form')
@section('content')
<div class="container py-4">

    <!-- Header -->
    <div class="mb-4">
        <h3>Supplier Evaluation Form</h3>
    </div>

    <!-- Committee Info -->
    <div class="mb-3 row">
        <label class="col-sm-2 col-form-label">Committee Member</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" value="John Doe" readonly>
        </div>
        <label class="col-sm-2 col-form-label">UserID</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" value="auto-populated" readonly>
        </div>
    </div>

    <!-- RFQ Section -->
    <div class="mb-3 row">
        <label class="col-sm-2 col-form-label">RFQ No</label>
        <div class="col-sm-4">
            <select class="form-select">
                <option>Select DropDown Or Search</option>
                <option>RFQ001</option>
                <option>RFQ002</option>
            </select>
        </div>
        <label class="col-sm-2 col-form-label">RFQ Description</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" placeholder="Load from DB" readonly>
        </div>
    </div>

    <!-- Supplier Table -->
    <div class="table-responsive mb-4">
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Suppliers</th>
                    <th>Total Quoted</th>
                    <th>Delivery Time</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Supplier A</td>
                    <td>Kes. 2,500</td>
                    <td>6 Days</td>
                    <td>Submitted</td>
                    <td><a href="#" class="btn btn-sm btn-link">View Quote</a></td>
                </tr>
                <tr>
                    <td>Supplier B</td>
                    <td>Kes. 3,000</td>
                    <td>5 Days</td>
                    <td>Submitted</td>
                    <td><a href="#" class="btn btn-sm btn-link">View Quote</a></td>
                </tr>
                <tr>
                    <td>Supplier C</td>
                    <td>-</td>
                    <td>-</td>
                    <td>No Reply</td>
                    <td><a href="#" class="btn btn-sm btn-link disabled">View Quote</a></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Evaluation Forms -->
    @for ($i = 1; $i <= 3; $i++)
    <div class="card mb-4">
        <div class="card-header">
            Supplier {{ $i }}
        </div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Evaluation Criteria</th>
                        <th>Weight (%)</th>
                        <th>Score (1-10)</th>
                        <th>Comments</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Technical Quality</td>
                        <td>40%</td>
                        <td><input type="number" class="form-control" min="1" max="10"></td>
                        <td><input type="text" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>Pricing</td>
                        <td>30%</td>
                        <td><input type="number" class="form-control" min="1" max="10"></td>
                        <td><input type="text" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>Delivery Time</td>
                        <td>20%</td>
                        <td><input type="number" class="form-control" min="1" max="10"></td>
                        <td><input type="text" class="form-control"></td>
                    </tr>
                    <tr>
                        <td>Past Experience</td>
                        <td>10%</td>
                        <td><input type="number" class="form-control" min="1" max="10"></td>
                        <td><input type="text" class="form-control"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    @endfor

    <!-- Confirmation Checkbox -->
    <div class="form-check mb-4">
        <input class="form-check-input" type="checkbox" value="" id="confirmCheck">
        <label class="form-check-label" for="confirmCheck">
            ✅ I confirm that this scoring is done independently and fairly.
        </label>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Submit</button>
        <button type="button" class="btn btn-secondary">Save</button>
        <button type="button" class="btn btn-danger">Cancel</button>
    </div>

</div>
@endsection
