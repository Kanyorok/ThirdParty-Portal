<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts._partials._head')
    <title>{{ config('app.name') }} - @yield('title')</title>
    {{-- <style>
         #sideNavBarMain {
             background: linear-gradient(to bottom, rgba(62, 115, 177, 0.96), rgba(66, 102, 145, 0.98)), url('{{ asset('assets/img/errors/giraffe.jpeg') }}') top center no-repeat;
             height: 100%;
             overflow: hidden;
         }
     </style>--}}
</head>

<body>

<div class="wrapper">
    <nav id="sidebar" class="sidebar js-sidebar">
        <div class="sidebar-content js-simplebar">
            <a class="sidebar-brand" href="{{ route('home') }}">
                <img src="{{{ asset('assets/img/icons/android-icon-36x36.png') }}}" alt=""><span
                    class="align-middle"> {{ config('app.name') }}</span>
            </a>

            <ul class="sidebar-nav" id="sideNavBarMain">
                <li class="sidebar-item {{ request()->is(['/'])?'active':'' }}">
                    <a class="sidebar-link " href="{{ route('home') }}">
                        <i class="align-middle" data-feather="home"></i> <span class="align-middle">Dashboard</span>
                    </a>
                </li>
                {{--<li class="sidebar-item {{ request()->is(['user/emails*'])?'active':'' }}">
                    <a class="sidebar-link " href="{{ route('emails.index') }}">
                        <i class="align-middle fas fa-envelope"></i> <span class="align-middle"> Mail Box</span>
                    </a>
                </li>--}}
                <li class="sidebar-item {{ request()->is(['email*'])?'active':'' }}">
                    <a class="sidebar-link " href="{{ route('email-conversations.index') }}">
                        <i class="align-middle fas fa-envelope"></i> <span class="align-middle"> Mail Box</span>
                    </a>
                </li>
                <li class="sidebar-item {{ request()->is(['user/schedule*'])?'active':'' }}">
                    <a class="sidebar-link " href="{{ route('schedule.index') }}">
                        <i class="align-middle fas fa-calendar"></i> <span class="align-middle"> My Schedule</span>
                    </a>
                </li>
                <li class="sidebar-item {{ request()->is(['tickets*'])?'active':'' }}">
                    <a class="sidebar-link" href="{{ route('tickets.index') }}">
                        <i class="align-middle" data-feather="check-square"></i> <span
                            class="align-middle"> Tickets</span> </a>
                </li>

                @can('thirdParties', App\Models\User::class)
                    <li class="sidebar-header">Third Parties</li>
                    @can('viewAny', App\Models\Client::class)
                        <li class="sidebar-item {{ request()->is(['clients*'])?'active':'' }}">
                            <a class="sidebar-link " href="{{ route('clients.index') }}">
                                <i class="align-middle" data-feather="users"></i> <span
                                    class="align-middle"> Members</span>
                            </a>
                        </li>
                    @endcan @can('viewAny', App\Models\Lead::class)
                        <li class="sidebar-item {{ request()->is(['leads*'])?'active':'' }}">
                            <a class="sidebar-link " href="{{ route('leads.index') }}">
                                <i class="align-middle fas fa-address-book"></i> <span
                                    class="align-middle"> Leads</span>
                            </a>
                        </li>
                    @endcan @can('viewAny', App\Models\Board::class)
                        <li class="sidebar-item {{ request()->is(['board*'])?'active':'' }}">
                            <a class="sidebar-link " href="{{ route('board.index') }}">
                                <i class="align-middle fas fa-user-secret"></i> <span
                                    class="align-middle"> Board </span>
                            </a>
                        </li>
                    @endcan
                @endcan

                @can('marketing', App\Models\User::class)
                    <li class="sidebar-header">Marketing</li>
                    @can('viewAny', App\Models\MarketingPlanner::class)
                        <li class="sidebar-item {{ request()->is(['marketing/marketing-planner*','marketing/master-planner*'])?'active':'' }}">
                            <a class="sidebar-link" href="{{ route('marketing-planner.index') }}">
                                <i class="align-middle fa-solid fa-seedling"></i> <span
                                    class="align-middle"> Planner </span>
                            </a>
                        </li>
                    @endcan @can('viewAny', App\Models\MarketingList::class)
                        <li class="sidebar-item {{ request()->is(['marketing/marketing-list*'])?'active':'' }}">
                            <a class="sidebar-link" href="{{ route('marketing-list.index') }}">
                                <i class="align-middle fas fa-list-dots"></i> <span
                                    class="align-middle"> Marketing Lists </span>
                            </a>
                        </li>
                    @endcan @can('viewAny', App\Models\Campaign::class)
                        <li class="sidebar-item {{ request()->is(['marketing/campaigns*'])?'active':'' }}">
                            <a class="sidebar-link" href="{{ route('campaigns.index') }}">
                                <i class="align-middle fas fa-copyright"></i> <span
                                    class="align-middle"> Campaigns</span>
                            </a>
                        </li>
                    @endcan @can('viewAny', App\Models\Competitor::class)
                        <li class="sidebar-item {{ request()->is(['marketing/competitors*'])?'active':'' }}">
                            <a class="sidebar-link" href="{{ route('competitors.index') }}">
                                <i class="align-middle fas fa-face-rolling-eyes"></i> <span
                                    class="align-middle">Competitors </span>
                            </a>
                        </li>
                    @endcan @can('viewAny', App\Models\Social::class)
                        <li class="sidebar-item {{ request()->is(['marketing/socials*'])?'active':'' }}">
                            <a class="sidebar-link" href="{{ route('socials.index') }}">
                                <i class="align-middle fa-solid fa-icons"></i><span
                                    class="align-middle"> Social Media</span></a>
                        </li>
                    @endcan
                @endcan

                @can('viewAny', App\Models\ProductDevelopment::class)
                    <li class="mt-3 sidebar-item {{ request()->is(['product-development*'])?'active':'' }}">
                        <a class="sidebar-link " href="{{ route('product-development.index') }}">
                            <i class="align-middle fa-solid fa-cubes"></i> <span class="align-middle"> Product Development</span>
                        </a>
                    </li>
                @endcan

                @can('debt', App\Models\User::class)
                    <li class="sidebar-header">Debt Collection</li>
                    @can('viewAny', App\Models\BulkNotification::class)
                        <li class="sidebar-item {{ request()->is(['debt-notification*'])?'active':'' }}">
                            <a class="sidebar-link " href="{{ route('debt-notification.index') }}">
                                <i class="align-middle fas fa-comment-dollar"></i> <span
                                    class="align-middle"> Notifications</span>
                            </a>
                        </li>
                    @endcan @can('viewAny', App\Models\BR\DebtProduct::class)
                        <li class="sidebar-item  {{ request()->is(['debt-collection*','loans-list*'])?'active':'' }}">
                            <a href="#DebtProductSideMenu" data-bs-toggle="collapse" class="sidebar-link collapsed">
                                <i class="align-middle fas fa-hands-helping"></i> <span
                                    class="align-middle"> Loans</span>
                            </a>
                            <ul id="DebtProductSideMenu"
                                class="sidebar-dropdown list-unstyled collapse {{ request()->is(['debt-collection*','loans-list*'])?'show':'' }}"
                                data-bs-parent="#sidebar">

                                <li class="sidebar-item {{ request()->is(['loans-list*'])?'active':'' }}"><a
                                        class="sidebar-link" href="{{ route('loans-list.index') }}">Lists</a></li>
                                <li class="sidebar-item {{ request()->is(['debt-collection*'])?'active':'' }}"><a
                                        class="sidebar-link" href="{{ route('debt-collection.index') }}">Loans</a></li>
                            </ul>
                        </li>
                    @endcan
                @endcan

                @can('feedback', App\Models\User::class)
                    <li class="sidebar-header">Feedback</li>
                    @can('viewAny', App\Models\Survey::class)
                        <li class="sidebar-item {{ request()->is(['surveys*'])?'active':'' }}">
                            <a class="sidebar-link" href="{{ route('surveys.index') }}">
                                <i class="align-middle fa-regular  fa-circle-check"></i> <span
                                    class="align-middle"> Surveys</span></a>
                        </li>
                    @endcan  @can('viewAny', App\Models\Review::class)
                        <li class="sidebar-item {{ request()->is(['reviews*'])?'active':'' }}">
                            <a class="sidebar-link" href="{{ route('reviews.index') }}">
                                <i class="align-middle fa-regular fa-comment"></i> <span
                                    class="align-middle"> Reviews</span>
                            </a>
                        </li>
                    @endcan
                @endcan
                @can('settings', App\Models\User::class)
                    <li class="sidebar-header">Settings</li>
                    @can('view', App\Models\CodeDetail::class)
                        <li class="sidebar-item {{ request()->is(['settings/lists'])?'active':'' }}">
                            <a class="sidebar-link" href="{{ route('settings.lists') }}">
                                <i class="align-middle fas fa-cog"></i> <span class="align-middle"> System Codes</span>
                            </a>
                        </li>
                    @endcan @can('settings', App\Models\User::class)
                        <li class="sidebar-item {{ request()->is(['settings/users-roles*','settings/teams*','settings/users*'])?'active':'' }}">
                            <a class="sidebar-link" href="{{ route('settings.users') }}">
                                <i class="align-middle fas fa-users-cog"></i>
                                <span class="align-middle"> Users & Roles</span>
                            </a>
                        </li>
                    @endcan @can('integrations', App\Models\APICredential::class)
                        <li class="sidebar-item {{ request()->is(['settings/integrations*'])?'active':'' }}">
                            <a class="sidebar-link" href="{{ route('settings.integrations') }}">
                                <i class="align-middle fas fa-cogs"></i>
                                <span class="align-middle"> Integrations</span>
                            </a>
                        </li>
                    @endcan
                @endcan
            </ul>
        </div>
    </nav>

    <div class="main">
        <nav class="navbar navbar-expand navbar-light navbar-bg">
            <a class="sidebar-toggle js-sidebar-toggle">
                <i class="hamburger align-self-center"></i>
            </a>
            <div class="text-danger d-none" id="sessionInactivity">
                <span id="sessionInactivityMinutes">00</span>:
                <span id="sessionInactivitySeconds">00</span>
            </div>
            <div class="navbar-collapse collapse">
                <ul class="navbar-nav navbar-align">
                    <li class="nav-item dropdown">
                        <a class="nav-icon dropdown-toggle" href="#" id="alertsDropdown" data-bs-toggle="dropdown">
                            <div class="position-relative">
                                <i class="align-middle" data-feather="bell"></i>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end py-0"
                             aria-labelledby="alertsDropdown">
                            <div class="dropdown-menu-header">
                                0 New Notifications
                            </div>
                            <div class="dropdown-menu-footer">
                                <a href="#" class="text-muted">Show all notifications</a>
                            </div>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-icon js-fullscreen d-none d-lg-block" href="#">
                            <div class="position-relative">
                                <i class="align-middle" data-feather="maximize"></i>
                            </div>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-icon dropdown-toggle d-inline-block d-sm-none" href="#" data-bs-toggle="dropdown">
                            <i class="align-middle" data-feather="settings"></i>
                        </a>

                        <a class="nav-link dropdown-toggle d-none d-sm-inline-block" href="#" data-bs-toggle="dropdown">
                            {!! auth()->user()->getImage('class="avatar-1 avatar img-fluid rounded me-1"') !!}
                            <span class="text-dark">{{ auth()->user()->UserID }}</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="{{ route('profile') }}"><i class="align-middle me-1"
                                                                                      data-feather="user"></i>
                                Profile</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#"><i class="align-middle me-1"
                                                                 data-feather="help-circle"></i> Help Center</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ route('logout') }}"
                               onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                {{ __('Logout') }}
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
        <main class="content p-4">
            <div class="container-fluid p-0">
                @yield('content')
            </div>
        </main>

        <footer class="footer">
            <div class="container-fluid">
                <div class="row text-muted">
                    <div class="col-6 text-start">
                        @include('layouts._partials._copyright')
                    </div>
                    <div class="col-6 text-end">
                        <ul class="list-inline">
                            <li class="list-inline-item">
                                @include('layouts._partials._version')
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</div>
@include('layouts._partials._scripts')
</body>
</html>
