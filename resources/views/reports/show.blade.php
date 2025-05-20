@extends('layouts.app')
@section('title')
    {{ $report->Name }}
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    {{-- autologin when you visit reports urls.
                <iframe frameborder="0" seamless="seamless" class="viewer" title="Report Viewer" src="http://172.16.2.13:7092/ReportServer/Pages/ReportViewer.aspx?%2FBRERP%2FAdmin%2FUsers&amp;rc:showbackbutton=true"></iframe>
http://172.16.2.13:7092/reports/report/BRERP/Admin/Permissions?rs:embed=true
               --}}
                    <iframe id="reportIframe" width="100%" height="600px" frameborder="0"
                            src="{{ $metadata['Route'] }}"></iframe>
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
