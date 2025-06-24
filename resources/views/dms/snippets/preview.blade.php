@php use App\Enums\Core\ExtensionsEnum; @endphp
<div class="row">
    <div class="col-12">
        @if($service->isPrevieable())
            @if($service->type->value === ExtensionsEnum::Pdf->value)
                <div>
                    {!! $service->preview('width="100%" height="100" style="min-height:70vh;"') !!}
                </div>

            @elseif($service->type->isImage())
                {!! $service->preview('class="img img-fluid"') !!}
            @endif

        @else
            <h6 class="text-center">{!! $service->type->getIcon() !!}</h6>
            <h3 class="text-center">No Preview Available Download Below</h3>
        @endif
    </div>
    {{--<div class="col-12 border-top">
        <form id="trashDocumentForm" action="{{ route('documents.destroy',[$image->ImageID])  }}"
              method="post" class="text-center my-2"> @csrf
            <a href="{{ route('documents.edit',[$image->ImageID]) }}" target="_blank" download
               class="btn btn-primary btn-lg mx-2"><i class="fas fa-download"></i> download</a>
            <button type="submit" class="btn btn-danger btn-lg mx-2" id="trashDocumentBtn"><i class="fas fa-trash"></i>
                trash
            </button>@method('delete')
        </form>
    </div>--}}
</div>
{{--<script>
    $(function () {
        $('form#trashDocumentForm').submit(async function (e) {
            e.preventDefault();
            if (await saveForm($(this), $('#trashDocumentBtn'), false, true, true)) {
                $("#document-{{ $image->ImageID }}").fadeOut();
                $Modal.modal('hide');
            }
        });
    });
</script>--}}

