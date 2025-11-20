<ul class="pc-submenu">
    @foreach ($children as $child)
        <li class="pc-item
        @if (!empty($child['children']))
            pc-hasmenu
        @elseif(is_string($child['route']) && request()->routeIs($child['route'] . '*'))
            active
        @endif
        ">
            @php $childPath = parse_url($child['route'], PHP_URL_PATH) ?? '/'; @endphp

                <!-- Submenu Link with HTMX -->
            <a class="pc-link sidebar-link"
               href="{{ $child['route'] }}"
               hx-get="{{ $child['route'] }}"
               hx-target="#page-content"
               hx-push-url="true"
               hx-swap="innerHTML"
               data-route="{{ $childPath }}">
                <span data-i18n="{{ $child['name'] }}">{{ $child['name'] }}</span>
                @if (!empty($child['children']))
                    <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                @endif
            </a>

            @if (!empty($child['children']))
                @include('layouts._partials._submenu', ['children' => $child['children']])
            @endif
        </li>
    @endforeach
</ul>
