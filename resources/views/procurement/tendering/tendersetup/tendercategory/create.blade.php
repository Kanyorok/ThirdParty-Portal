@extends('layouts.app')
@section('title', 'Item Sub Category')
@section('content')
<div class="container mt-4">
    <h4 class="mb-4">Tender Initiation Form</h4>
    <form>
        <!-- Title -->
        <div class="mb-3">
            <label for="tenderTitle" class="form-label fw-bold">Tender Title:</label>
            <input type="text" class="form-control" id="tenderTitle" placeholder="Enter tender title">
        </div>

        <!-- Tender Type -->
        <div class="mb-3">
            <label class="form-label fw-bold">Tender Type:</label>
            <div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="tenderType" id="openTender" value="Open">
                    <label class="form-check-label" for="openTender">Open Tender (Public posting)</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="tenderType" id="restrictedTender" value="Restricted">
                    <label class="form-check-label" for="restrictedTender">Restricted Tender (Selected vendors only)</label>
                </div>
            </div>
        </div>

        <!-- Category and Requisition -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="tenderCategory" class="form-label fw-bold">Tender Category:</label>
                <select class="form-select" id="tenderCategory">
                    <option selected disabled>-- Select Category --</option>
                    <option>Goods</option>
                    <option>Services</option>
                    <option>Works</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="relatedPR" class="form-label fw-bold">Related PR No: <small>(optional)</small></label>
                <select class="form-select" id="relatedPR">
                    <option selected disabled>-- Search Requisitions --</option>
                    <option>PR/2025/001</option>
                    <option>PR/2025/002</option>
                </select>
            </div>
        </div>

        <!-- Scope -->
        <div class="mb-3">
            <label for="scopeOfWork" class="form-label fw-bold">Scope of Work</label>
            <textarea class="form-control" id="scopeOfWork" rows="3"></textarea>
        </div>

        <!-- Instructions -->
        <div class="mb-3">
            <label for="instructions" class="form-label fw-bold">Instructions to Bidders:</label>
            <textarea class="form-control" id="instructions" rows="3"></textarea>
        </div>

        <!-- Dates -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="submissionDeadline" class="form-label fw-bold">Submission Deadline:</label>
                <input type="date" class="form-control" id="submissionDeadline">
            </div>
            <div class="col-md-6 mb-3">
                <label for="openingDate" class="form-label fw-bold">Opening Date:</label>
                <input type="date" class="form-control" id="openingDate">
            </div>
        </div>

        <!-- Upload -->
        <div class="mb-3">
            <label for="tenderDocuments" class="form-label fw-bold">Attach Tender Documents:</label>
            <input class="form-control" type="file" id="tenderDocuments" multiple>
        </div>

        <!-- Conditional Suppliers List -->
        <div class="mb-3" id="restrictedSuppliersSection" style="display: none;">
            <label for="suppliersList" class="form-label fw-bold">Add Suppliers to Invite:</label>
            <select class="form-select" id="suppliersList" multiple>
                <option>Supplier A - Tech Supplies Ltd</option>
                <option>Supplier B - Nova Solutions</option>
                <option>Supplier C - EquiBuild Ltd</option>
            </select>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Publish Tender</button>
            <button type="button" class="btn btn-outline-secondary">Save Draft</button>
            <button type="reset" class="btn btn-outline-dark">Cancel</button>
            <button type="button" class="btn btn-outline-info">Edit</button>
        </div>
    </form>
</div>

<!-- Script to toggle supplier selection -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const open = document.getElementById('openTender');
        const restricted = document.getElementById('restrictedTender');
        const section = document.getElementById('restrictedSuppliersSection');

        open.addEventListener('change', () => section.style.display = 'none');
        restricted.addEventListener('change', () => section.style.display = 'block');
    });
</script>
@endsection
