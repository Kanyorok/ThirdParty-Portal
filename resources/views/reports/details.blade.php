@extends('layouts.app')
@section('title')
    test
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    {!! $reportHtml !!}
                </div>
            </div>
        </div>
    </div>

@endsection
@section('scripts')
    <script>
        /* $(function () {

             window.onload = loadReport;
         });
         function loadReport() {
             document.getElementById("reportIframe").src = "&StartDate=2025-01-01&EndDate=2025-01-31";
         }*/

    </script>
@endsection
