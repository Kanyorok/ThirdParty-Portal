@extends('layouts.app')
@section('title', 'Upload Claim Documents')

@section('content')
    <div class="container mt-4">
        <h4>📂 Upload Claim Documents</h4>

        <form action="{{ route('bancassurance.claims.documents.upload', $claim->Id) }}" method="POST"
              enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label for="DocumentType" class="form-label">Document Type</label>
                <select name="DocumentType" id="DocumentType" class="form-select" required onchange="toggleOtherType()">
                    <option value="">-- Select Type --</option>
                    @foreach($documentTypes as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                    <option value="Other">Other (Specify Below)</option>
                </select>
            </div>

            <div class="mb-3 d-none" id="otherTypeDiv">
                <label for="OtherType" class="form-label">Specify Other Document Type</label>
                <input type="text" name="OtherType" id="OtherType" class="form-control"
                       placeholder="Enter other document type">
            </div>

            <div class="mb-3">
                <label for="DocumentName" class="form-label">Document Name / Description</label>
                <input type="text" name="DocumentName" class="form-control"
                       placeholder="e.g. Medical Report from Nairobi Hospital" required>
            </div>

            <div class="mb-3">
                <label for="DocumentFile" class="form-label">Choose File</label>
                <input type="file" name="DocumentFile" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">Upload</button>
        </form>


        <hr>

        <h5 class="mt-4">📄 Uploaded Documents</h5>
        @if(!empty($documents) && count($documents) > 0)
            <ul class="list-group">
                @foreach($documents as $doc)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $doc->DocumentType }}</strong> – {{ $doc->DocumentName }}
                        </div>
                        <a href="{{ asset('storage/' . $doc->FilePath) }}" target="_blank"
                           class="btn btn-sm btn-outline-primary">View</a>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-muted">No documents uploaded yet.</p>
        @endif
    </div>
    @push('scripts')
        <script>
            function toggleOtherType() {
                const dropdown = document.getElementById('DocumentType');
                const otherDiv = document.getElementById('otherTypeDiv');
                if (dropdown.value === 'Other') {
                    otherDiv.classList.remove('d-none');
                    document.getElementById('OtherType').required = true;
                } else {
                    otherDiv.classList.add('d-none');
                    document.getElementById('OtherType').required = false;
                }
            }
        </script>
    @endpush
@endsection
