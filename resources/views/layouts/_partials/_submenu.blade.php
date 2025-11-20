<ul class="pc-submenu">
    @foreach ($children as $child)
    <li class="pc-item {{ !empty($child['children']) ? 'pc-hasmenu' : '' }}">
        @php $childPath = parse_url($child['route'], PHP_URL_PATH) ?? '/'; @endphp
        <a class="pc-link ajax-link" href="{{ $child['route'] }}" data-ajax="1" data-route="{{ $childPath }}">
            <span data-i18n="{{ $child['name'] }}">{{ $child['name'] }}</span>
            @if (!empty($child['children']))
            <span class="pc-arrow ajax-link"><i data-feather="chevron-right"></i></span>
            @endif
        </a>

        @if (!empty($child['children']))
        @include('layouts._partials._submenu', ['children' => $child['children']])
        @endif
    </li>
    @endforeach
</ul>