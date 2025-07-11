@extends('layouts.app')
@section('title', 'Create Inspection Report')

@section('content')
    <div class="container mt-4">
        <h4 class="mb-3">🔍 Inspection Report</h4>
        <form method="POST" action="#">
            @csrf
            <div class="mb-3">
                <label for="delivery_note" class="form-label">Delivery Note</label>
                <select class="form-select" id="delivery_note" name="delivery_note">
                    <option selected disabled>-- Select Delivery Note --</option>
                    <option value="DN-001">DN-001 - PO-789</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="inspected_by" class="form-label">Inspected By</label>
                <input type="text" class="form-control" value="John Mugo" readonly>
            </div>

            <label class="form-label">Inspection Details</label>
            <table class="table table-sm table-bordered">
                <thead>
                <tr>
                    <th>Item</th>
                    <th>Delivered</th>
                    <th>Accepted</th>
                    <th>Rejected</th>
                    <th>Remarks</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>HP Printer</td>
                    <td>2</td>
                    <td><input type="number" class="form-control" value="2"></td>
                    <td><input type="number" class="form-control" value="0"></td>
                    <td><input type="text" class="form-control" placeholder="OK"></td>
                </tr>
                <tr>
                    <td>A4 Paper Box</td>
                    <td>10</td>
                    <td><input type="number" class="form-control" value="9"></td>
                    <td><input type="number" class="form-control" value="1"></td>
                    <td><input type="text" class="form-control" placeholder="1 box damaged"></td>
                </tr>
                </tbody>
            </table>

            <button type="submit" class="btn btn-success">Submit Inspection</button>
        </form>
    </div>
@endsection
