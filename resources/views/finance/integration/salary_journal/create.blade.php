@extends('layouts.app')
@section('title', 'Add Salary Journal Template')

@section('content')
    <div class="container mt-4">
        <h4 class="mb-3">➕ Add Salary Journal Template</h4>

        <form action="{{ route('salary-journal-templates.store') }}" method="POST">
            @csrf

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Template Name</label>
                    <input type="text" name="TemplateName" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Component Code</label>
                    <select name="ComponentCode" class="form-select" required>
                        <option value="BASIC">BASIC</option>
                        <option value="HRA">HRA</option>
                        <option value="PAYE">PAYE</option>
                        <option value="NHIF">NHIF</option>
                        <option value="NSSF">NSSF</option>
                        <option value="ALLOW">ALLOWANCE</option>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Debit GL Account</label>
                    <input type="text" name="DebitGLAccount" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Credit GL Account</label>
                    <input type="text" name="CreditGLAccount" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Branch</label>
                    <input type="text" name="BranchCode" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Department</label>
                    <input type="text" name="DepartmentCode" class="form-control">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="IsActive" class="form-select">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>

            <button type="submit" class="btn btn-success">💾 Save Template</button>
        </form>
    </div>
@endsection
