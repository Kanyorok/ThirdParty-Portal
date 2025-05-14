@extends('layouts.app')
@section('title', 'Import Needs from Department')
@section('content')
<!-- Modal: Import from Needs -->
<div class="modal fade" id="importNeedsModal" tabindex="-1" aria-labelledby="importNeedsLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">📥 Import Needs from Departments</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <p>Select items from approved departmental needs to include in this plan:</p>
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th><input type="checkbox" /></th>
                            <th>Branch</th>
                            <th>Department</th>
                            <th>Item Description</th>
                            <th>Qty</th>
                            <th>Est. Cost</th>
                            <th>Justification</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Sample Row -->
                        <tr>
                            <td><input type="checkbox" /></td>
                            <td>Nairobi HQ</td>
                            <td>ICT</td>
                            <td>Desktop Computers</td>
                            <td>12</td>
                            <td>720,000</td>
                            <td>End of life equipment</td>
                        </tr>
                        <tr>
                            <td><input type="checkbox" /></td>
                            <td>Finance</td>
                            <td>Mombasa</td>
                            <td>Calculators</td>
                            <td>20</td>
                            <td>80,000</td>
                            <td>Operational use</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary">Add Selected to Plan</button>
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection
