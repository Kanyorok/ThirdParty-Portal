@php
  use Carbon\Carbon;
  use Illuminate\Support\Facades\Storage;
@endphp

@extends('layouts.app')

@section('title', 'Manual Bid Submissions')

@section('styles')
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('content')
  <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4>Manual Bid Submissions</h4>
      <a href="{{ route('tendersubmission.create') }}" class="btn btn-sm btn-success">+ Record Manual Submission</a>
    </div>

    <div class="table-responsive">
      <table id="bidsubmissionTable" class="table table-bordered table-striped align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Tender Ref</th>
            <th>Supplier</th>
            <th>Submission Mode</th>
            <th>Received At</th>
            <th>Recorded By</th>
            <th>Remarks</th>
            <th>Documents</th>
            <th>Actions</th>
          </tr>
        </thead>

        <tbody>
          @forelse ($submissions as $index => $submission)
            <tr>
              <td>{{ $index + 1 }}</td>
              <td>{{ $submission->TenderRef }}</td>
              <td>{{ $submission->SupplierName }}</td>
              <td>{{ $submission->submissionMode->Description ?? 'N/A' }}</td>
              <td>
                @if ($submission->ReceivedAt instanceof \Illuminate\Support\Carbon || $submission->ReceivedAt instanceof \Carbon\Carbon)
                  {{ $submission->ReceivedAt->format('d/m/Y') }}
                @else
                  {{ \Carbon\Carbon::parse($submission->ReceivedAt)->format('d/m/Y') }}
                @endif
              </td>
              <td>{{ $submission->createdByUser->Name ?? 'N/A' }}</td>
              <td>{{ $submission->Remarks ?? 'N/A' }}</td>
              <td>
                @if ($submission->Documents && Storage::exists($submission->Documents))
                  <a href="{{ Storage::url($submission->Documents) }}" class="btn btn-sm btn-link" download>Download</a>
                @else
                  N/A
                @endif
              </td>
              <td>
                <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                  data-bs-target="#viewModal-{{ $submission->Id }}">View</button>

                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                  data-bs-target="#editModal-{{ $submission->Id }}" hidden>Edit</button>
              </td>
            </tr>

            {{-- View Modal (scoped to this $submission) --}}
            <div class="modal fade" id="viewModal-{{ $submission->Id }}" tabindex="-1"
              aria-labelledby="viewModalLabel-{{ $submission->Id }}" aria-hidden="true">
              <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="viewModalLabel-{{ $submission->Id }}">📋 Bid Submission Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="container">
                      <div class="row mb-3">
                        <div class="col-md-6">
                          <label class="fw-bold">Tender Ref:</label>
                          <div class="text-muted">{{ $submission->TenderRef ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6">
                          <label class="fw-bold">Supplier:</label>
                          <div class="text-muted">{{ $submission->SupplierName ?? 'N/A' }}</div>
                        </div>
                      </div>

                      <div class="row mb-3">
                        <div class="col-md-6">
                          <label class="fw-bold">Submission Mode:</label>
                          <div class="text-muted">{{ $submission->submissionMode->Description ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6">
                          <label class="fw-bold">Received At:</label>
                          <div class="text-muted">
                            @if ($submission->ReceivedAt instanceof \Illuminate\Support\Carbon || $submission->ReceivedAt instanceof \Carbon\Carbon)
                              {{ $submission->ReceivedAt->format('d/m/Y') }}
                            @else
                              {{ \Carbon\Carbon::parse($submission->ReceivedAt)->format('d/m/Y') }}
                            @endif
                          </div>
                        </div>
                      </div>

                      <div class="row mb-3">
                        <div class="col-md-6">
                          <label class="fw-bold">Recorded By:</label>
                          <div class="text-muted">{{ $submission->createdByUser->Name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6">
                          <label class="fw-bold">Remarks:</label>
                          <div class="text-muted">{{ $submission->Remarks ?? 'N/A' }}</div>
                        </div>
                      </div>

                      <div class="row mb-2">
                        <div class="col-md-12">
                          <label class="fw-bold">Documents:</label>
                          <div class="text-muted">
                            @if ($submission->Documents && Storage::exists($submission->Documents))
                              <a href="{{ Storage::url($submission->Documents) }}" class="btn btn-sm btn-link"
                                download>Download</a>
                            @else
                              N/A
                            @endif
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                  </div>
                </div>
              </div>
            </div>

            {{-- Edit Modal (scoped to this $submission) --}}
            <div class="modal fade" id="editModal-{{ $submission->Id }}" tabindex="-1"
              aria-labelledby="editModalLabel-{{ $submission->Id }}" aria-hidden="true">
              <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel-{{ $submission->Id }}">✏️ Edit Bid Submission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <form method="POST" action="{{ route('tendersubmission.update', $submission->Id) }}"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                      <div class="container">
                        <div class="row mb-3">
                          <div class="col-md-6">
                            <label class="form-label fw-bold">Tender Reference</label>
                            <input type="text" class="form-control" name="TenderRef"
                              value="{{ $submission->TenderRef }}" required>
                          </div>
                          <div class="col-md-6">
                            <label class="form-label fw-bold">Supplier Name</label>
                            <input type="text" class="form-control" name="SupplierName"
                              value="{{ $submission->SupplierName }}" required>
                          </div>
                        </div>

                        <div class="row mb-3">
                          <div class="col-md-6">
                            <label class="form-label fw-bold">Submission Mode</label>
                            <input type="text" class="form-control"
                              value="{{ $submission->submissionMode->Description ?? 'N/A' }}" readonly>
                            <input type="hidden" name="SubmissionModeID" value="{{ $submission->SubmissionModeID }}">
                          </div>
                          <div class="col-md-6">
                            <label class="form-label fw-bold">Received At</label>
                            @php
                              // datetime-local requires: Y-m-d\TH:i
                              $dtVal =
                                  $submission->ReceivedAt instanceof \Illuminate\Support\Carbon ||
                                  $submission->ReceivedAt instanceof \Carbon\Carbon
                                      ? $submission->ReceivedAt
                                      : \Carbon\Carbon::parse($submission->ReceivedAt);
                              $dtLocal = $dtVal->format('Y-m-d\TH:i');
                            @endphp
                            <input type="datetime-local" class="form-control" name="ReceivedAt"
                              value="{{ $dtLocal }}" required>
                          </div>
                        </div>

                        <div class="row mb-3">
                          <div class="col-md-6">
                            <label class="form-label fw-bold">Recorded By</label>
                            <input type="text" class="form-control"
                              value="{{ $submission->createdByUser->Name ?? 'N/A' }}" disabled>
                          </div>
                          <div class="col-md-6">
                            <label class="form-label fw-bold">Remarks</label>
                            <textarea class="form-control" name="Remarks" rows="2">{{ $submission->Remarks }}</textarea>
                          </div>
                        </div>

                        <div class="row mb-3">
                          <div class="col-md-12">
                            <label class="form-label fw-bold">Replace Document (optional)</label>
                            <input type="file" class="form-control" name="Documents">
                            @if ($submission->Documents && Storage::exists($submission->Documents))
                              <small class="text-muted d-block mt-1">
                                Current: <a href="{{ Storage::url($submission->Documents) }}" target="_blank">Download
                                  existing</a>
                              </small>
                            @endif
                          </div>
                        </div>

                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="submit" class="btn btn-primary">Update</button>
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          @empty
            <tr>
              <td colspan="9" class="text-center">No submissions found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Scripts --}}
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script>
    $(document).ready(function() {
      @if (!$submissions->isEmpty())
        $('#bidsubmissionTable').DataTable({
          pageLength: 10,
          ordering: true,
          searching: true,
          lengthChange: true,
          language: {
            emptyTable: ""
          }
        });
      @endif
    });
  </script>
@endsection
