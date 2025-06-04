@extends('layouts.app')
@section('title')
    {{ $report->Name }}
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card" id="reportMainBody">
                <div class='my-4 text-center'><i class="fas fa-spinner fa-spin fa-5x"></i><h3>Report Please wait ...</h3></div>
            </div>
        </div>
    </div>

@endsection
@section('scripts')
    <script>
         $(function () {
            getReport();
         });

         function getReport() {
             $.get(getDocumentUrl(), function (data) {
                 $("#reportMainBody").html(data);
             }).fail(function (jqXHR) {
                 nError(jqXHR.responseJSON.message);
                 $("#reportMainBody").html("<div class='my-4 text-center'><h3>"+jqXHR.responseJSON.message+"</h3></div>")
             });
         }
         /*$(function () {

             window.onload = loadReport;
         });
         function loadReport() {
             document.getElementById("reportIframe").src = "&StartDate=2025-01-01&EndDate=2025-01-31";
         }*/

    </script>
@endsection
