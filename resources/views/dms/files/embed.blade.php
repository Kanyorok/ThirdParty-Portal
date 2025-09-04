<div class="row">
    <div class="col-12" style="min-height: 100px" id="FilePreviewPage">
        @include('dms.files.preview')
    </div>
    <div class="col-12 border-top">
        <div class="row mt-2 text-center">
            <div class="col-6">
                <a href="{{ route('files.show',[$file->repository->RepositoryId, $file->DocumentId]) }}"
                   class="btn btn-secondary mx-2"><i class="fas fa-eye"></i> details</a>
            </div>
            <form action="{{ route('file-download.store',[$file->DocumentId]) }}" method="post"
                  id="fileDownloadForm" class="col-6"> @csrf
                <input type="hidden" name="fetch_link" value="{{ $file->Name }}" class="d-none">
                <button class="btn btn-secondary mx-2 " id="fileDownloadBtn" type="submit">
                    <i class="fas fa-file-download"></i> download
                </button>
            </form>
        </div>

    </div>
</div>
<script>
    $(function () {
        $('form#fileDownloadForm').submit(async function (e) {
            e.preventDefault();
            const data = await saveForm($(this), $('#fileDownloadBtn'), false, true, true);
            if (data) {
                window.open(data.route, '_blank', 'noopener,noreferrer');
            }
        });
    });
</script>

