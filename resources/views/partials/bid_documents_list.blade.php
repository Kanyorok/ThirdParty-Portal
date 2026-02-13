@if(count($documents) > 0)
<div class="table-responsive">
    <table class="table table-sm table-hover">
        <thead>
            <tr>
                <th>Document</th>
                <th>Size</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($documents as $doc)
            <tr>
                <td>
                    <i class="fas fa-file-alt text-primary me-2"></i>
                    {{ $doc['filename'] ?? 'Unknown Document' }}
                </td>
                <td>{{ $doc['size'] ?? 'N/A' }}</td>
                <td>
                    @if(isset($doc['dms_document']) && $doc['dms_document'])
                        {{-- Use DocumentService for DMS-backed documents --}}
                        {!! (new \App\Services\DMS\DocumentService($doc['dms_document']))->summaryList() !!}
                    @elseif(isset($doc['document_id']) && $doc['document_id'])
                        {{-- DMS document ID exists but document wasn't loaded - show view link --}}
                        <a href="{{ route('file.embed-preview', ['document' => $doc['document_id']]) }}" 
                           class="btn btn-sm btn-outline-primary modal-preview-document"
                           data-url="{{ route('file.embed-preview', ['document' => $doc['document_id']]) }}"
                           title="View {{ $doc['filename'] }}">
                            <i class="fas fa-eye"></i> View
                        </a>
                    @elseif($doc['can_view'] ?? false)
                        {{-- Legacy format - use controller method --}}
                        <button class="btn btn-sm btn-outline-primary" 
                                onclick="viewDocument({{ $bidId }}, '{{ $doc['id'] }}')"
                                title="View Document">
                            <i class="fas fa-eye"></i> View
                        </button>
                    @else
                        <span class="badge bg-secondary">🔒 Sealed</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
<div class="p-3 border rounded bg-light">
    <span class="text-muted">No documents attached.</span>
</div>
@endif
