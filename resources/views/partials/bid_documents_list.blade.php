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
                        {{-- DMS-backed document - use bid-responsiveness route for preview --}}
                        <span class="btn btn-sm btn-outline-primary modal-preview-document"
                              title="{{ $doc['filename'] }}"
                              data-url="{{ url('/procurement/bid-responsiveness/' . $bidId . '/document/' . $doc['dms_document']->DocumentId) }}"
                              id="document-{{ $doc['dms_document']->DocumentId }}">
                            <i class="fas fa-eye"></i> View
                        </span>
                    @elseif(isset($doc['document_id']) && $doc['document_id'])
                        {{-- DMS document ID exists but document wasn't loaded --}}
                        <span class="btn btn-sm btn-outline-primary modal-preview-document"
                              title="{{ $doc['filename'] }}"
                              data-url="{{ url('/procurement/bid-responsiveness/' . $bidId . '/document/' . $doc['document_id']) }}">
                            <i class="fas fa-eye"></i> View
                        </span>
                    @elseif($doc['can_view'] ?? false)
                        {{-- Legacy format - no DMS backing --}}
                        <span class="badge bg-warning text-dark" title="Preview not available for legacy uploads">
                            <i class="fas fa-exclamation-triangle"></i> Preview N/A
                        </span>
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