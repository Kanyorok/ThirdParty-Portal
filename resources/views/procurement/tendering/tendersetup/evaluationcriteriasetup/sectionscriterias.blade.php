@extends('layouts.app')
@section('title', 'Tenders Criteria')
@section('content')


<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>📑 All Evaluation Criteria</h4>
        <a href="#" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addSection1Modal">
            + New Section</a>
    </div>
 
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
                        <a href="{{route('sectioncriterias.show',1)}}" class="btn btn-sm btn-outline-primary">View</a>
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
            <form action="#" method="POST" enctype="multipart/form-data" id="sectionCriteriaForm">
                @csrf
                @method('POST')

                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Tender Title</label>
                            <select name="tender_title" class="form-control form-select" id="tender_title" required>
                                <option selected disabled>-- Select Tender title --</option>
                                @foreach ($tenders as $item)
                                    <option value="{{ $item->Id }}">{{ $item->TenderNo }} | {{ $item->Title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 10%">Include</th>
                                <th>Section / Criteria</th>
                                <th style="width: 20%">Weight (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sections as $section)
                                {{-- Section Row --}}
                                <tr class="table-primary section-row">
                                    <td>
                                        <input type="checkbox" name="sections[]" value="{{ $section->id }}" class="section-checkbox">
                                    </td>
                                    <td class="fw-bold">{{ $section->SectionName }}</td>
                                    <td>
                                        <input type="number" class="form-control weight-input" name="weights[{{ $section->id }}]" value="0.00" step="0.01" min="0" max="100">
                                    </td>
                                </tr>

                                {{-- Sample criterias --}}
                                @php
                                    $sampleCriterias = ['Compliance Check', 'Experience Evaluation', 'Capacity Assessment'];
                                @endphp
                                @foreach ($sampleCriterias as $index => $criteria)
                                    <tr class="criteria-row">
                                        <td>
                                            <input type="checkbox" name="criterias[]" value="sample_{{ $section->id }}_{{ $index }}">
                                        </td>
                                        <td class="ps-4">→ {{ $criteria }}</td>
                                        <td class="text-muted text-center">N/A</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="text-end fw-bold">Total Weight</td>
                                <td><strong id="totalWeight">0.00</strong>%</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="saveCriteriaBtn">Save Sections</button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection
