<div class="row">
    <div class="col-12" style="min-height: 100px" id="FilePreviewPage">
        @include('dms.files.preview')
    </div>
    <div class="col-12 border-top">
        <div class="row my-2 text-center">
            <div class="col-4">
                <a href="{{ route('files.show',[$file->repository->RepositoryId, $file->DocumentId]) }}"
                   class="btn btn-secondary mx-2"><i class="fas fa-eye"></i> details</a>
            </div>
            <form action="{{ route('file-download.store',[$file->DocumentId]) }}" method="post"
                  id="fileDownloadForm" class="col-4"> @csrf
                <input type="hidden" name="fetch_link" value="{{ $file->Name }}" class="d-none">
                <button class="btn btn-secondary mx-2 " id="fileDownloadBtn" type="submit">
                    <i class="fas fa-file-download"></i> download
                </button>
            </form>
            <div class="col-4">
                @if($withTrash)
                    @can('delete', $file)
                        <form id="trashFileForm" method="post" action="{{  route('files.destroy', [$file->repository->RepositoryId, $file->DocumentId]) }}"> @csrf
                            @method('delete')
                            <button class="btn btn-secondary mx-2" id="trashFileBtn"
                                    type="submit">
                                <i class="fas fa-trash"></i> delete
                            </button>
                        </form>
                    @endcan
                @else
                    <button class="btn btn-secondary mx-2 disabled" disabled type="button">
                        <i class="fas fa-trash"></i> delete
                    </button>
                @endif
            </div>
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

        $('form#trashFileForm').submit(async function (e) {
            e.preventDefault();
            const confirmed = window.confirm(`Delete {{ $file->Name }}? This action can't be undone.`);
            if (!confirmed) return;

            const response = await saveForm($(this), $('#trashFileBtn'), Boolean('{{ $trashRefresh }}'), true, true);
            if (response) {
                $('#' + response.data.id).remove();
            }
        });
    });
</script>
