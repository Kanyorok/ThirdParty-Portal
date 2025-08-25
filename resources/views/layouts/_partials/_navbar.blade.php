@php use App\Services\Core\ModuleService; @endphp
<ul class="pc-navbar">
  <li class="pc-item {{ request()->is('/') ? 'active' : '' }}">
    <a href="{{ route('home') }}" class="pc-link" data-ajax="1">
      <span class="pc-micon">
        <i data-feather="home" class="pc-icon"></i>
      </span>
      <span class="pc-mtext fw-bold" data-i18n="Data">Home</span>
    </a>
  </li>
  {!! ModuleService::generateNavbar() !!}
  {{-- @foreach (ModuleService::generateNavbar() as $module)
         <li class="pc-item
             @if (!empty($module['children']))
                 @if (request()->is(\Illuminate\Support\Str::of($module['name'])->ucfirst()->lower()->toString() . '*'))
                      active pc-trigger
                 @endif
                 pc-hasmenu
             @elseif(is_string($module['route']) && request()->route()->named($module['route']))
                 active
             @endif
             ">
             <a href="{{ $module['route'] }}" class="pc-link">
                 <span class="pc-micon">
                     {!! $module['icon'] ?? '<i data-feather="box"></i>' !!}
                 </span>
                 <span class="pc-mtext">{{ $module['name'] }}</span>
                 @if (!empty($module['children']))
                     <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                 @endif
             </a>

             @if (!empty($module['children']))
                 @include('layouts._partials._submenu', ['children' => $module['children']])
             @endif
         </li>
     @endforeach --}}
</ul>
