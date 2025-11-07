@extends('layouts.app')
@section('title', 'Issue Stock to Branch')
@section('content')

    <div class="card mb-4">
        <div class="card-header bg-warning text-dark">📤 Issue Stock to Branch</div>
        <div class="card-body">
            <form>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">PR Reference</label>
                        <select class="form-select">
                            <option>Select Purchase Requisition</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Requesting Branch</label>
                        <input type="text" class="form-control" readonly value="Branch A">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Issue Date</label>
                        <input type="date" class="form-control"/>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Issued By</label>
                    <input type="text" class="form-control" placeholder="Storekeeper name">
                </div>

                <div class="mb-3">
                    <label class="form-label">Items to Issue</label>
                    <table class="table table-bordered">
                        <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Requested Qty</th>
                            <th>Issued Qty</th>
                            <th>UOM</th>
                            <th>Remarks</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td><input class="form-control"/></td>
                            <td><input class="form-control" readonly value="20"/></td>
                            <td><input class="form-control"/></td>
                            <td><input class="form-control"/></td>
                            <td><input class="form-control"/></td>
                            <td>
                                <button class="btn btn-danger btn-sm">Remove</button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <button class="btn btn-secondary btn-sm">➕ Add Item</button>
                </div>
                <button type="submit" class="btn btn-success"
                        onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();">Submit Stock
                    Issue
                </button>
            </form>
        </div>
    </div>

@endsection
