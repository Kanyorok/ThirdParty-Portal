@php use Carbon\Carbon; @endphp
    <!DOCTYPE html>
<html>
<head>
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"
           integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">--}}
    <link href="{{ asset('assets/css/light.css') }}" rel="stylesheet">
    <title>@yield('title')</title>
    <style>
        .table-bordered table, .table-bordered th, .table-bordered td {
            border: 1px solid #2f2f2f;
            border-collapse: collapse;
            padding: 5px;
        }

        .w-20 {
            width: 20%;
        }

        .border-bottom-cell {
            border-bottom: 1px solid #2f2f2f;
        }

        .text-faded {
            color: rgba(175, 174, 174, 0.73);
        }
    </style>
    @yield('style')
</head>
<body>
{{--<header class="text-faded text-end">
    <div class="pagenum-container">Page <span class="pagenum"></span></div>
</header>--}}
<div class="wrapper" style="width: 100%;">
    <table class="table-borderless table table-responsive" style="width: 100%;">
        <tr>
            <td class="w-25 m-0 p-0" style="width: 25vw;"><img
                    src="{{ asset('assets/img/icons/android-icon-96x96.png') }}" alt=""
                    class="img img-fluid"></td>
            <td style="width: 75vw; text-align: center;">
                <h1 class="h1 mb-1" style="margin-bottom: 1px; text-transform: uppercase;">{{ config('org.name') }}</h1>
                <h5 class="h4 fw-normal">{{ config('org.address') }}
                    <br> {{ config('org.phone') }}  {{ config('org.email') }}  {{ config('org.website') }}</h5>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <hr class="m-0 p-0">
                <h1 class="h5 p-2 text-decoration-underline text-center text-uppercase">@yield('title')</h1>
            </td>
        </tr>
    </table>
    @yield('content')
</div>
{{--<footer class="text-faded text-end">
    <div class="text-start">
       &copy; {{ config('app.name') }}
    </div>
    <div class="text-end">
        Developed and Powered By Craftsilicon
    </div>
</footer>--}}
</body>
</html>
