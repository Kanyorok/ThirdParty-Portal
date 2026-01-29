@extends('layouts.app')
@section('title', 'Bulk Upload Property Floors')

@section('content')

<style>
    .bulk-upload-section {
        max-width: 900px;
        margin: 0 auto;
    }
    
    .upload-info-box {
        background: #f8f9fa;
        border-left: 4px solid #007bff;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border-radius: 4px;
    }
    
    .upload-info-box h5 {
        color: #007bff;
        margin-bottom: 0.75rem;
        font-weight: 600;
    }
    
    .upload-info-box ul {
        margin-bottom: 0;
        padding-left: 1.5rem;
    }
    
    .upload-info-box li {
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }
    
    .form-section {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 2rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .file-upload-wrapper {
        position: relative;
        border: 2px dashed #007bff;
        border-radius: 4px;
        padding: 2rem;
        text-align: center;
        background: #f0f7ff;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .file-upload-wrapper:hover {
        border-color: #0056b3;
        background: #e6f2ff;
    }
    
    .file-upload-wrapper.has-file {
        border-color: #28a745;
        background: #f0fff4;
    }
    
    .file-upload-wrapper input[type="file"] {
        display: none;
    }
    
    .upload-icon {
        font-size: 2.5rem;
        color: #007bff;
        margin-bottom: 0.75rem;
    }
    
    .file-name {
        color: #28a745;
        font-weight: 600;
        margin-top: 0.75rem;
    }
    
    .template-section {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 1rem;
        margin-top: 1.5rem;
        border-radius: 4px;
    }
    
    .template-section a {
        color: #ff6600;
        text-decoration: none;
        font-weight: 600;
    }
    
    .template-section a:hover {
        text-decoration: underline;
    }
    
    .error-summary {
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        border-radius: 4px;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .error-summary h6 {
        color: #721c24;
        margin-bottom: 0.75rem;
        font-weight: 600;
    }
    
    .error-item {
        color: #721c24;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }
</style>

<div class="bulk-upload-section py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-cloud-upload-alt"></i> Bulk Upload Property Floors
        </h2>
        <a href="{{ route('addfloor.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Floors
        </a>
    </div>

    {{-- Error Summary --}}
    @if ($errors->any())
        <div class="error-summary">
            <h6>Upload Errors:</h6>
            @if ($errors->has('file'))
                @foreach ($errors->get('file') as $error)
                    <div class="error-item">{{ $error }}</div>
                @endforeach
            @endif
            @if (session('errors'))
                @foreach (session('errors') as $error)
                    <div class="error-item">Row {{ $error['row'] }}: {{ $error['error'] }}</div>
                @endforeach
            @endif
        </div>
    @endif

    {{-- Success Message --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Warning Message --}}
    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Info Boxes --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="upload-info-box">
                <h5><i class="fas fa-info-circle"></i> Required Columns</h5>
                <ul>
                    <li><strong>PropertyID</strong> - Property ID</li>
                    <li><strong>BlockID</strong> - Block ID</li>
                    <li><strong>FloorLabel</strong> - Floor label/name</li>
                    <li><strong>FloorNotes</strong> - Optional notes</li>
                </ul>
            </div>
        </div>

        <div class="col-md-6">
            <div class="upload-info-box">
                <h5><i class="fas fa-check-double"></i> Best Practices</h5>
                <ul>
                    <li>Use CSV or Excel (.xlsx) files</li>
                    <li>Maximum file size: 10MB</li>
                    <li>Include headers in first row</li>
                    <li>PropertyID & BlockID must exist</li>
                    <li>FloorLabel must be unique per block</li>
                    <li>FloorNotes is optional</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Upload Form --}}
    <div class="form-section">
        <form action="{{ route('addfloor.bulkStore') }}" method="POST" enctype="multipart/form-data" id="bulkUploadForm">
            @csrf

            <div class="mb-4">
                <label for="fileInput" class="form-label fw-600">Select File to Upload</label>
                <div class="file-upload-wrapper" id="fileUploadArea">
                    <div class="upload-icon">
                        <i class="fas fa-file-excel"></i>
                    </div>
                    <p class="mb-0">
                        <strong>Click to upload</strong> or drag and drop<br>
                        <small class="text-muted">CSV or Excel files only (max 10MB)</small>
                    </p>
                    <div class="file-name" id="fileName"></div>
                    <input type="file" id="fileInput" name="file" accept=".csv,.xlsx,.xls" required>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-cloud-upload-alt"></i> Upload & Process
                </button>
                <a href="{{ route('addfloor.bulkTemplate') }}" class="btn btn-outline-info" target="_blank">
                    <i class="fas fa-download"></i> Download Template
                </a>
                <a href="{{ route('addfloor.index') }}" class="btn btn-outline-secondary">
                    Cancel
                </a>
            </div>
        </form>

        <div class="template-section">
            <strong><i class="fas fa-lightbulb"></i> Tip:</strong> Download the template to see the correct file format and sample data.
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('fileInput');
    const fileUploadArea = document.getElementById('fileUploadArea');
    const fileName = document.getElementById('fileName');
    const submitBtn = document.getElementById('submitBtn');

    // Drag and drop functionality
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        fileUploadArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        fileUploadArea.addEventListener(eventName, () => {
            fileUploadArea.style.borderColor = '#0056b3';
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        fileUploadArea.addEventListener(eventName, () => {
            fileUploadArea.style.borderColor = '#007bff';
        });
    });

    fileUploadArea.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        fileInput.files = files;
        updateFileName();
    });

    fileUploadArea.addEventListener('click', () => {
        fileInput.click();
    });

    fileInput.addEventListener('change', updateFileName);

    function updateFileName() {
        if (fileInput.files.length > 0) {
            fileName.textContent = '✓ ' + fileInput.files[0].name;
            fileUploadArea.classList.add('has-file');
        } else {
            fileName.textContent = '';
            fileUploadArea.classList.remove('has-file');
        }
    }

    // Form submission
    document.getElementById('bulkUploadForm').addEventListener('submit', function(e) {
        if (!fileInput.files.length) {
            e.preventDefault();
            alert('Please select a file to upload');
        }
    });
});
</script>

@endsection
