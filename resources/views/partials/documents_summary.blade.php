
<div class="p-3 border rounded bg-light">
    @forelse($documents as $document)
        {!! (new \App\Services\DMS\DocumentService($document))->summaryList() !!}
    @empty
        <span class="text-muted">No documents attached.</span>
    @endforelse
</div>
