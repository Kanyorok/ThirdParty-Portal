@extends('layouts.app')
@section('title', 'Segment Configuration')
@section('content')
    <div class="container mt-2">

        <!-- Modal Trigger Buttons -->
        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#segmentOrderModal">
            Display Order
        </button>
        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#glDigitsModal">
            GL Digits
        </button>

        <!-- Add Segment Type -->
        <div class="card mb-4">
            <div class="card-header">Add Segment Type</div>
            <div class="card-body">
                <form method="POST" action="#">
                    <div class="mb-3">
                        <label class="form-label">Segment Code</label>
                        <input type="text" name="SegmentCode" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Segment Name</label>
                        <input type="text" name="SegmentName" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Segment</button>
                </form>
            </div>
        </div>

        <!-- Segment Values -->
        <div class="card">
            <div class="card-header">Segment Values</div>
            <div class="card-body">
                <form method="POST" action="#">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Select Segment</label>
                            <select name="SegmentID" class="form-select">
                                <option value="1">BRANCH - Branch</option>
                                <option value="2">DEPT - Department</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Segment Value Code</label>
                            <input type="text" name="SegmentValueCode" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Segment Value Name</label>
                            <input type="text" name="SegmentValueName" class="form-control">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success">Add Segment Value</button>
                </form>

                <!-- Segment Value List -->
                <hr>
                <h6 class="mt-4">Existing Segment Values</h6>
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>Segment</th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>BRANCH</td>
                        <td>001</td>
                        <td>HQ</td>
                        <td><span class="badge bg-success">Active</span></td>
                    </tr>
                    <tr>
                        <td>DEPT</td>
                        <td>100</td>
                        <td>Finance</td>
                        <td><span class="badge bg-success">Active</span></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal for Segment Order -->
    <div class="modal fade" id="segmentOrderModal" tabindex="-1" aria-labelledby="segmentOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('segment-order.save') }}">
                @csrf
                @method('post')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Segment Order</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <ul id="segmentList" class="list-group">
                            @foreach($segments as $segment)
                                <li class="list-group-item d-flex align-items-center justify-content-between border rounded mb-2 shadow-sm p-3 bg-light"
                                    data-segment="{{ $segment->SegmentType }}">
                                    <span class="fw-bold">{{ $segment->SegmentType }}</span>
                                    <i class="fas fa-grip-vertical fs-4 text-muted drag-handle"></i>
                                </li>
                            @endforeach
                        </ul>
                        <input type="hidden" name="segment_order" id="segmentOrderInput">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-success" type="button" onclick="submitSegmentOrder(this)">
                            Save Order
                        </button>

                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal for GL Digits -->
    <div class="modal fade" id="glDigitsModal" tabindex="-1" aria-labelledby="glDigitsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('glDigits.save') }}">
                @csrf
                @method('post')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">GL Digits</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">GL Digits</label>
                        <input type="number" min="1" name="glDigits" value="{{ $glDigits }}" class="form-control" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-success" type="submit" onclick="if(this.form.checkValidity()){ this.disabled=true; this.innerText='Saving...'; this.form.submit();}">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/Sortable/Sortable.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const segmentList = document.getElementById('segmentList');

            if (segmentList) {
                new Sortable(segmentList, {
                    animation: 150,
                    handle: '.drag-handle',
                    ghostClass: 'bg-warning-subtle',
                });
            }
        });

        function submitSegmentOrder(button) {
            const form = button.form;

            // Extract SegmentType and Description
            const items = document.querySelectorAll('#segmentList li');
            const order = Array.from(items).map(item => item.dataset.segment); // just SegmentType

            document.getElementById('segmentOrderInput').value = JSON.stringify(order);

            // Submit safely
            if (form.checkValidity()) {
                button.disabled = true;
                button.innerText = 'Saving...';
                form.submit();
            }
        }
    </script>

@endsection
