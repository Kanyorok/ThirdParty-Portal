<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts._partials._head')
    <title> {{ config('app.name') }} - @yield('title')</title>
    <style>
        body {
            background-image: url("{{ asset('assets/img/homescreen2_r1_c2.png') }}");
            background-size: cover;
            background-position: center;
            background-position-x: center;
            background-position-y: center;
            position: fixed;
            width: 100%;
            height: 100vh;
            overflow-y: hidden;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3vh;
        }

        .card {
            box-shadow: 0 0 .875rem 0 #2d3091;
            margin-bottom: 24px;
        }
    </style>
</head>
<body>
<main class="d-flex w-100" style="background: #20242b3d;">
    <div class="container d-flex flex-column">
        <div class="row vh-100">
            <div class="col-sm-10 col-md-6 col-lg-5 mx-auto d-table h-100">
                <div class="d-table-cell align-middle">

                    @yield('content')
                </div>
            </div>
        </div>
    </div>
    <div class="footer text-center mb-1">
        <p class="text-white">
            @include('layouts._partials._copyright') | @include('layouts._partials._version')
        </p>
    </div>
</main>
@include('layouts._partials._scripts')
</body>
</html>

{{--
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts._partials._head')
    <title> {{ config('app.name') }} - @yield('title')</title>
</head>
<body>
<main class="d-flex w-100">
    <div class="container d-flex flex-column">
        <div class="row vh-100">
            <div class="col-sm-10 col-md-6 col-lg-5 mx-auto d-table h-100">
                <div class="d-table-cell align-middle">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

</main>
@include('layouts._partials._scripts')
</body>
</html>
--}}
