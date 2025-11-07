@php use App\Enums\Core\ExtensionsEnum; @endphp
<div class="w-100">
    @if($service->isPrevieable())
        @if($service->type->value === ExtensionsEnum::Pdf->value)
            <div>
                {!! $service->preview('width="100%" height="100" style="min-height:70vh;"') !!}
            </div>
        @elseif($service->type->value === ExtensionsEnum::Txt->value)
            <div>
                {!! $service->preview('width="100%" height="100" style="min-height:70vh;"') !!}
            </div>
        @elseif($service->type->isImage())
            {!! $service->preview('class="img img-fluid"') !!}
        @endif

    @else
        <h6 class="text-center"><img src="{{ $service->type->getIcon('img') }}" alt="user-image" class="wid-75">
        </h6>
        <h3 class="text-center">No Preview Available Download Below</h3>
    @endif
</div>
