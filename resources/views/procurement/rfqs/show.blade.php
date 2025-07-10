@php use Carbon\Carbon; @endphp
@extends('layouts.app')
@section('title', 'RFQ Details')
@section('content')
    <div class="container">
        <button type="button" class="btn btn-primary mb-3"
                data-bs-toggle="modal" data-bs-target="#createRFQModal"
                @if($rfq->rfqLines->where('RFQId', $rfq->Id)->count()) disabled @endif>
            + New RFQ Line
        </button>
        <div id="printSection">
            <div class="card mb-3">
                <div class="card-body">
                    <p><strong>RFQ Number:</strong> {{ $rfq->RFQNumber }}</p>
                    <p><strong>RFQ Comments:</strong> {{ $rfq->Comments }}</p>
                    <p><strong>Requisition No:</strong> {{ $rfq->requisition->RequisitionNo }}</p>
                    <p><strong>Status:</strong>
                        <span class="badge
                    @if($rfq->Status === 'Pending') bg-warning
                    @elseif($rfq->Status === 'Approved') bg-success
                    @elseif($rfq->Status === 'Rejected') bg-danger
                    @else bg-secondary @endif">
                    {{ $rfq->Status }}
                </span>
                    </p>
                    <p><strong>RFQ Reject Remarks:</strong> {{ $rfq->Remarks }}</p>

                </div>
            </div>

            <h5>Requisition Items Details:</h5>
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>RFQ Line No</th>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>UOM</th>
                    <th>Submission Deadline</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($rfq->rfqLines as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->RFQLineNo }}</td>
                        <td>{{ $item->ItemName}}</td>
                        <td>{{ $item->Quantity }}</td>
                        <td>{{ $item->uom->Name}}</td>
                        <td>{{ Carbon::parse($rfq->SubmissionDeadline)->format('d/m/Y') }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                <tr>
                    <td colspan="2" class="text-center">
                        @if ($rfq->Status === 'Pending')
                            <form id="approveForm" action="{{ route('rfqs.approve', $rfq->Id) }}" method="POST">
                                @csrf
                                <button type="button"
                                        class="btn btn-success btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#approveModal"
                                    {{ $rfq->rfqLines->isEmpty() ? 'disabled title=Please add at least one RFQ line' : '' }}>
                                    Approve
                                </button>
                            </form>
                        @endif

                    </td>

                    <td colspan="2" class="text-center">
                        @if ($rfq->Status === 'Pending')
                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#rejectModal"
                                {{ $rfq->rfqLines->isEmpty() ? 'disabled title=Please add at least one RFQ line' : '' }}>
                                Reject
                            </button>
                        @endif
                    </td>

                    {{--            <td class="text-center">--}}
                    {{--                @if ($rfq->Status === 'Approved')--}}
                    {{--                    <button type="button" class="btn btn-primary btn-sm">Save</button>--}}
                    {{--                @endif--}}
                    {{--            </td>--}}


                </tr>
                </tfoot>

            </table>
        </div>
        <div class="text-end">
            @if ($rfq->Status === 'Approved')
                <button type="button" class="btn btn-secondary btn-sm" onclick="printRFQ()">Print</button>

            @endif
        </div>
        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('rfqs.reject', $rfq->Id) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="rejectModalLabel">Reject RFQ</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="RejectionReason" class="form-label">Reason for Rejection</label>
                                <textarea name="RejectionReason" id="RejectionReason" class="form-control" rows="3"
                                          required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Reject</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Approve Modal -->
        <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form id="supplierSelectionForm" action="{{ route('rfqs.approve', $rfq->Id) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="approveModalLabel">Select Suppliers</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="suppliers" class="form-label">Suppliers</label>
                                <select name="suppliers[]" id="suppliers" class="form-control" multiple>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->Id }}">{{ $supplier->SupplierName }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">Send Emails & Approve</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="createRFQModal" tabindex="-1" aria-labelledby="createRFQModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('linecategories.store') }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createRFQModalLabel">Create RFQ Line</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <!-- Requisition No (readonly) -->
                    <div class="mb-3">
                        <label for="requisitionNo">Requisition No</label>
                        <input type="text" class="form-control" id="requisitionNo"
                               value="{{ $rfq->requisition->RequisitionNo ?? 'N/A' }}" readonly>
                    </div>

                    <!-- Item Category Dropdown -->
                    <div class="mb-3">
                        <label for="categoryDropdown">Item Category</label>
                        <select name="ItemCategoryId" id="categoryDropdown" class="form-control" required>
                            <option value="">-- Select Category --</option>
                        </select>
                    </div>
                </div>

                <input type="hidden" name="RFQId" id="rfq-number" value="{{ $rfq->Id }}">
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save RFQ Line</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const categoryDropdown = document.getElementById('categoryDropdown');
            const requisitionId = {{ $rfq->requisition->Id }};

            categoryDropdown.innerHTML = '<option value="">-- Select Category --</option>';

            fetch(`/procurement/requisition/${requisitionId}/categories`)
                .then(response => response.json())
                .then(data => {
                    if (!data || data.length === 0) {
                        const option = document.createElement('option');
                        option.value = "";
                        option.textContent = "⚠️ No items available for the attached requisition.";
                        categoryDropdown.appendChild(option);
                        categoryDropdown.disabled = false;
                    } else {
                        data.forEach(cat => {
                            const option = document.createElement('option');
                            option.value = cat.Id;
                            option.textContent = cat.Name;
                            categoryDropdown.appendChild(option);
                        });
                        categoryDropdown.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error loading categories:', error);
                    categoryDropdown.innerHTML = '<option value="">⚠️ Failed to load categories</option>';
                    categoryDropdown.disabled = true;
                });
        });
    </script>


    <script>
        function printRFQ() {
            const content = document.getElementById('printSection').innerHTML;
            const printWindow = window.open('', '', 'height=800,width=1000');
            printWindow.document.write(`
            <html>
                <head>
                    <title>Print RFQ</title>
                    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
                    <style>
                        table, th, td {
                            border: 1px solid #000;
                            border-collapse: collapse;
                        }
                        th, td {
                            padding: 8px;
                            text-align: left;
                        }
                        body {
                            font-family: Arial, sans-serif;
                            padding: 20px;
                        }
                    </style>
                </head>
                <body>
                    ${content}
                </body>
            </html>
        `);
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const approveForm = document.getElementById('supplierSelectionForm');
            const suppliersSelect = document.getElementById('suppliers');

            approveForm.addEventListener('submit', function (e) {
                const selected = Array.from(suppliersSelect.options).filter(option => option.selected);

                if (selected.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one supplier before approving.');
                }
            });
        });
    </script>

@endsection
