<ul class="pc-submenu">
  @foreach ($children as $child)
    <li class="pc-item {{ !empty($child['children']) ? 'pc-hasmenu' : '' }}">
      <a class="pc-link ajax-link" href="{{ $child['route'] }}" data-ajax="1">
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
