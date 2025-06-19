@extends('layouts.app')
@section('title', 'Assign Evaluation Sections')
@section('content')
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">➕ Add Evaluation Section</div>
        <div class="card-body">
            <form id="sectionForm">
                <div class="mb-3">
                    <label class="form-label">Section Name</label>
                    <input type="text" class="form-control" placeholder="e.g. Technical, Financial, Legal">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" rows="2"
                              placeholder="Describe the purpose of this section"></textarea>
                </div>
                <button type="submit" class="btn btn-success">Save Section</button>
            </form>
        </div>
    </div>


    INDEX

    <div class="card mb-4">
        <div class="card-header bg-light">📁 Evaluation Section Master</div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead class="table-secondary">
                <tr>
                    <th>#</th>
                    <th>Section Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>1</td>
                    <td>Technical</td>
                    <td>Assesses technical capacity and qualifications</td>
                    <td>
                        <a href="#" class="btn btn-sm btn-outline-primary">Add Criteria</a>
                        <a href="#" class="btn btn-sm btn-outline-danger">Delete</a>
                    </td>
                </tr>
                <!-- More rows -->
                </tbody>
            </table>
        </div>
    </div>


    <div class="card mb-4">
        <div class="card-header bg-info text-white">➕ Add Criteria to Section: <strong>Technical</strong></div>
        <div class="card-body">
            <form id="criteriaForm">
                <div class="mb-3">
                    <label class="form-label">Criterion Name</label>
                    <input type="text" class="form-control" placeholder="e.g. Experience, Compliance, Methodology">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" rows="2" placeholder="What will be evaluated?"></textarea>
                </div>
                <button type="submit" class="btn btn-success">Save Criterion</button>
            </form>
        </div>
    </div>


    INDEX

    <div class="card mb-4">
        <div class="card-header bg-light">📊 Criteria under Section: <strong>Technical</strong></div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Criterion</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>1</td>
                    <td>Experience</td>
                    <td>Years and quality of relevant past projects</td>
                    <td>
                        <a href="#" class="btn btn-sm btn-outline-danger">Remove</a>
                    </td>
                </tr>
                <!-- More rows -->
                </tbody>
            </table>
        </div>
    </div>


    Assign Sections to Tender/RFQ – Bootstrap Form

    <div class="card mb-4">
        <div class="card-header bg-success text-white">📑 Assign Evaluation Sections to Tender</div>
        <div class="card-body">
            <form id="tenderSectionWeightForm">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Tender Title</label>
                        <input type="text" class="form-control" value="TENDER/ICT/2025/001" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tender Type</label>
                        <input type="text" class="form-control" value="Restricted Tender" readonly>
                    </div>
                </div>

                <table class="table table-bordered">
                    <thead class="table-light">
                    <tr>
                        <th>Include</th>
                        <th>Section</th>
                        <th>Weight (%)</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td><input type="checkbox" checked></td>
                        <td>Technical</td>
                        <td><input type="number" class="form-control" value="60.00" step="0.01"></td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" checked></td>
                        <td>Financial</td>
                        <td><input type="number" class="form-control" value="30.00" step="0.01"></td>
                    </tr>
                    <tr>
                        <td><input type="checkbox"></td>
                        <td>Legal</td>
                        <td><input type="number" class="form-control" value="0.00" step="0.01" disabled></td>
                    </tr>
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="2" class="text-end fw-bold">Total</td>
                        <td><strong id="totalWeight">90.00</strong>%</td>
                    </tr>
                    </tfoot>
                </table>

                <button type="submit" class="btn btn-primary">Save Section Weights</button>
            </form>
        </div>
    </div>


    Criteria Setup.docx

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>📑 Evaluation Criteria Templates</h4>
            <a href="{{ route('evaluationcriteria.create') }}" class="btn btn-sm btn-success">+ New Criteria</a>
        </div>

        <!-- Optional Filter -->
        <div class="row mb-3">
            <div class="col-md-3">
                <select class="form-select">
                    <option selected>Filter by Type</option>
                    <option>Tender</option>
                    <option>RFQ</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select">
                    <option selected>Filter by Section</option>
                    <option>Technical</option>
                    <option>Financial</option>
                    <option>Legal</option>
                </select>
            </div>
        </div>

        <!-- Index Table -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Criteria No</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Section</th>
                    <th>Total Items</th>
                    <th>Total Weight</th>
                    <th>Max Score</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <!-- Example Row -->
                <tr>
                    <td>1</td>
                    <td>CRIT-2025-001</td>
                    <td><span class="badge bg-info">Tender</span></td>
                    <td>ICT Equipment</td>
                    <td>Technical</td>
                    <td>5</td>
                    <td>100</td>
                    <td>50</td>
                    <td>
                        <a href="/evaluation-criteria/view/1" class="btn btn-sm btn-outline-primary">View</a>
                        <a href="/evaluation-criteria/edit/1" class="btn btn-sm btn-outline-success">Edit</a>
                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                    </td>
                </tr>

                <tr>
                    <td>2</td>
                    <td>CRIT-2025-002</td>
                    <td><span class="badge bg-warning">RFQ</span></td>
                    <td>General Supplies</td>
                    <td>Technical</td>
                    <td>3</td>
                    <td>60</td>
                    <td>30</td>
                    <td>
                        <a href="/evaluation-criteria/view/2" class="btn btn-sm btn-outline-primary">View</a>
                        <a href="/evaluation-criteria/edit/2" class="btn btn-sm btn-outline-success">Edit</a>
                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                    </td>
                </tr>
                <!-- More rows dynamically -->
                </tbody>
            </table>
        </div>
    </div>

@endsection









@extends('layouts.app')
@section('title', 'Technical Criteria')
@section('content')

    <!-- Items Table -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-3">📦 Criteria</h5>
                <h4></h4>
                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                        data-bs-target="#addSectionModal">
                    + Add Criteria
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped1 align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Criteria Name</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>1</td>
                        <td>Experience</td>
                        <td>Years and quality of relevant past projects</td>
                        <td>
                            <a href="#" class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="#" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"
                                        onclick="return confirm('Are you sure you want to delete tender \'cr\'? This action cannot be undone.')">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>

                    </tbody>
                    <tfoot class="table-light fw-bold text-end">
                    </tfoot>
                </table>
            </div>
        </div>
    </div>


    <!-- Add Section Modal -->
    <div class="modal fade" id="addSectionModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content rounded-3 shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="addItemModalLabel">Add New Criteria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="#" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('POST')

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Criteria Name</label>
                            <input type="text" class="form-control"
                                   placeholder="e.g. Experience, Compliance, Methodology">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" rows="2"
                                      placeholder="Describe the purpose of this criteria"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button
                            type="submit"
                            class="btn btn-success"
                            onclick="this.disabled=true; this.innerText='Submitting...'; this.form.submit();"
                        >
                            Save Criteria
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>





























    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>📑 All Evaluation Criteria</h4>
            <a href="#" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addSection1Modal">
                + New Section</a>
        </div>

        <!-- Optional Filter -->
        {{-- <div class="row mb-3">
            <div class="col-md-3">
                <select class="form-select">
                    <option selected>Filter by Type</option>
                    <option>Tender</option>
                    <option>RFQ</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select">
                    <option selected>Filter by Section</option>
                    <option>Technical</option>
                    <option>Financial</option>
                    <option>Legal</option>
                </select>
            </div>
        </div> --}}

        <!-- Index Table -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Tender Ref</th>
                    <th>Category</th>
                    <th>Sections</th>
                    <th>Criteria Items</th>
                    <th>Total Weight</th>
                    <th>Max Score</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <!-- Example Row -->
                <tr>
                    <td>1</td>
                    <td>CRIT-2025-001</td>
                    <td>ICT Equipment</td>
                    <td>3</td>
                    <td>5</td>
                    <td>100</td>
                    <td>50</td>
                    <td>
                        <a href="/evaluation-criteria/view/1" class="btn btn-sm btn-outline-primary">View</a>
                        <a href="/evaluation-criteria/edit/1" class="btn btn-sm btn-outline-success">Edit</a>
                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                    </td>
                </tr>

                <!-- More rows dynamically -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Section Modal -->
    <div class="modal fade" id="addSection1Modal" tabindex="-1" aria-labelledby="addSectionLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content rounded-3 shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="addItemModalLabel">Add New Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="#" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('POST')

                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Tender Title</label>
                                <select name="tender_title" class="form-control form-select" id="tender_title">
                                    <option selected>--Select Tender title--</option>
                                    <option value="TENDER/ICT/2025/001">TENDER/ICT/2025/001</option>
                                    <option value="TENDER/FIN/2025/002">TENDER/FIN/2025/002</option>
                                </select>
                            </div>
                        </div>

                        <table class="table table-bordered">
                            <thead class="table-light">
                            <tr>
                                <th>Include</th>
                                <th>Section</th>
                                <th>Weight (%)</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td><input type="checkbox" name="sections[]" value="Technical" checked></td>
                                <td>Technical</td>
                                <td><input type="number" class="form-control weight-input" name="weights[]"
                                           value="60.00" step="0.01"></td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" name="sections[]" value="Financial" checked></td>
                                <td>Financial</td>
                                <td><input type="number" class="form-control weight-input" name="weights[]"
                                           value="30.00" step="0.01"></td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" name="sections[]" value="Legal"></td>
                                <td>Legal</td>
                                <td><input type="number" class="form-control weight-input" name="weights[]" value="0.00"
                                           step="0.01" disabled></td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" name="sections[]" value="Legal"></td>
                                <td>Legal</td>
                                <td><input type="number" class="form-control weight-input" name="weights[]" value="0.00"
                                           step="0.01" disabled></td>
                            </tr>
                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="2" class="text-end fw-bold">Total</td>
                                <td><strong id="totalWeight">90.00</strong>%</td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button
                            type="submit"
                            class="btn btn-success"
                            id="saveCriteriaBtn"
                        >
                            Save Criteria
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Inline JavaScript to enforce 100% weight -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('addSection1Modal');
            if (!modal) return;

            const form = modal.querySelector('form');
            const totalWeightDisplay = document.getElementById('totalWeight');
            const submitBtn = document.getElementById('saveCriteriaBtn');

            function updateTotal() {
                let total = 0;
                const rows = form.querySelectorAll('tbody tr');

                rows.forEach(row => {
                    const checkbox = row.querySelector('input[type="checkbox"]');
                    const weightInput = row.querySelector('input[type="number"]');

                    if (checkbox.checked) {
                        weightInput.disabled = false;
                        total += parseFloat(weightInput.value) || 0;
                    } else {
                        weightInput.disabled = true;
                    }
                });

                totalWeightDisplay.textContent = total.toFixed(2);
                return total;
            }

            // Listen to checkbox and number input changes
            form.querySelectorAll('tbody tr').forEach(row => {
                const checkbox = row.querySelector('input[type="checkbox"]');
                const weightInput = row.querySelector('input[type="number"]');

                checkbox.addEventListener('change', updateTotal);
                weightInput.addEventListener('input', updateTotal);
            });

            // Validate on submit
            form.addEventListener('submit', function (e) {
                const total = updateTotal();
                if (total.toFixed(2) !== '100.00') {
                    e.preventDefault();
                    alert('Total weight must be exactly 100.00%. Current total: ' + total.toFixed(2) + '%');
                }
            });

            // Initialize on load
            updateTotal();
        });
    </script>
























    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>📑 Tender Criteria</h4>

            <!-- Optional Filter -->
            {{-- <div class="row mb-3">
                <div class="col-md-3">
                    <select class="form-select">
                        <option selected>Filter by Type</option>
                        <option>Tender</option>
                        <option>RFQ</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select">
                        <option selected>Filter by Section</option>
                        <option>Technical</option>
                        <option>Financial</option>
                        <option>Legal</option>
                    </select>
                </div>
            </div> --}}

            <!-- Index Table -->
            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Section Name</th>
                        <th>Total Weight</th>
                        <th>Criteria Items</th>
                        <th>Max Score</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <!-- Example Row -->
                    <tr>
                        <td>1</td>
                        <td>Technical</td>
                        <td>100</td>
                        <td>3
                            <button class="btn btn-bd-primary"><i class="fa fa-eye"></i></button>
                        </td>
                        <td>30</td>
                        <td>
                            <a href="/evaluation-criteria/view/1" class="btn btn-sm btn-outline-primary">View</a>
                            <a href="/evaluation-criteria/edit/1" class="btn btn-sm btn-outline-success">Edit</a>
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </td>
                    </tr>

                    <!-- More rows dynamically -->
                    </tbody>
                </table>
            </div>
        </div>

@endsection
