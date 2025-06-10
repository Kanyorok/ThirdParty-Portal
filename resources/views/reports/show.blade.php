@extends('layouts.app')
@section('title')
    {{ $report->Name }}
@endsection
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/libs/select2/css/select2.min.css') }}">
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            @if(count($parameters)>0)
                <form class="card p-2" id="reportParametersForm">
                    <div class="row">
                        <div class="col-10">
                            <div class="row">
                                @foreach($parameters as $parameter)
                                    @if($parameter['ParameterType'] === 'DateTime')
                                        <div class="col-md-4 col-4 mb-3">
                                            <label for="{{ $parameter['Name'] }}"
                                                   class="form-label">{{ $parameter['Prompt'] }} {!! (!$parameter['Nullable'] && !$parameter['AllowBlank']) ? '<span class="text-danger" title="required">*</span>' : '' !!}</label>
                                            <input
                                                type="date"
                                                class="form-control"
                                                id="{{ $parameter['Name'] }}"
                                                name="{{ $parameter['Name'] }}"
                                                required="{{ !$parameter['Nullable'] && !$parameter['AllowBlank'] ? 'true' : 'false' }}"
                                            >
                                            @if($parameter['ErrorMessage'])
                                                <div
                                                    class="form-text text-danger">{{ $parameter['ErrorMessage'] }}</div>
                                            @endif
                                        </div>
                                    @elseif($parameter['ParameterType'] === 'String')
                                        <div class="col-md-4 col-4 mb-3">
                                            <label for="{{ $parameter['Name'] }}"
                                                   class="form-label">{{ $parameter['Prompt'] }} {!! (!$parameter['Nullable'] && !$parameter['AllowBlank']) ? '<span class="text-danger" title="required">*</span>' : '' !!}</label>
                                            @if($parameter['MultiValue'] && !$parameter['ValidValuesIsNull'] && count($parameter['ValidValues']) > 0)
                                                <select
                                                    class="form-select select-option"
                                                    id="{{ $parameter['Name'] }}"
                                                    name="{{ $parameter['Name'] }}"
                                                    required="{{ !$parameter['Nullable'] && !$parameter['AllowBlank'] ? 'true' : 'false' }}"
                                                    multiple
                                                >
                                                    @foreach($parameter['ValidValues'] as $value)
                                                        <option
                                                            value="{{ $value['Value'] }}" {{ in_array($value['Value'], $parameter['DefaultValues'], true) ? 'selected' : '' }}>{{ $value['Label'] }}</option>
                                                    @endforeach
                                                </select>
                                            @elseif(!$parameter['ValidValuesIsNull'] && count($parameter['ValidValues']) > 0)
                                                <select
                                                    class="form-select select-option"
                                                    id="{{ $parameter['Name'] }}"
                                                    name="{{ $parameter['Name'] }}"
                                                    required="{{ !$parameter['Nullable'] && !$parameter['AllowBlank'] ? 'true' : 'false' }}"
                                                >
                                                    @foreach($parameter['ValidValues'] as $value)
                                                        <option
                                                            value="{{ $value['Value'] }}" {{ in_array($value['Value'], $parameter['DefaultValues'], true) ? 'selected' : '' }}>{{ $value['Label'] }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    id="{{ $parameter['Name'] }}"
                                                    name="{{ $parameter['Name'] }}"
                                                    required="{{ !$parameter['Nullable'] && !$parameter['AllowBlank'] ? 'true' : 'false' }}"
                                                    value="{{ !empty($parameter['DefaultValues']) ? $parameter['DefaultValues'][0] : '' }}"
                                                >
                                            @endif
                                            @if($parameter['ErrorMessage'])
                                                <div
                                                    class="form-text text-danger">{{ $parameter['ErrorMessage'] }}</div>
                                            @endif
                                        </div>
                                    @elseif($parameter['ParameterType'] === 'Boolean')
                                        <div class="col-md-4 col-4 mb-3">
                                            <label for="{{ $parameter['Name'] }}"
                                                   class="form-label">{{ $parameter['Prompt'] }} {!! (!$parameter['Nullable'] && !$parameter['AllowBlank']) ? '<span class="text-danger" title="required">*</span>' : '' !!}</label>
                                            <div class="form-check">
                                                <input
                                                    type="radio"
                                                    class="form-check-input"
                                                    id="{{ $parameter['Name'] }}_true"
                                                    name="{{ $parameter['Name'] }}"
                                                    value="true"
                                                    required="{{ !$parameter['Nullable'] && !$parameter['AllowBlank'] ? 'true' : 'false' }}"
                                                >
                                                <label class="form-check-label"
                                                       for="{{ $parameter['Name'] }}_true">Yes</label>
                                            </div>
                                            <div class="form-check">
                                                <input
                                                    type="radio"
                                                    class="form-check-input"
                                                    id="{{ $parameter['Name'] }}_false"
                                                    name="{{ $parameter['Name'] }}"
                                                    value="false"
                                                    required="{{ !$parameter['Nullable'] && !$parameter['AllowBlank'] ? 'true' : 'false' }}"
                                                >
                                                <label class="form-check-label"
                                                       for="{{ $parameter['Name'] }}_false">No</label>
                                            </div>
                                            @if($parameter['ErrorMessage'])
                                                <div
                                                    class="form-text text-danger">{{ $parameter['ErrorMessage'] }}</div>
                                            @endif
                                        </div>
                                    @elseif($parameter['ParameterType'] === 'Float')
                                        <div class="col-md-4 col-4 mb-3">
                                            <label for="{{ $parameter['Name'] }}"
                                                   class="form-label">{{ $parameter['Prompt'] }} {!! (!$parameter['Nullable'] && !$parameter['AllowBlank']) ? '<span class="text-danger" title="required">*</span>' : '' !!}</label>
                                            <input
                                                type="number"
                                                step="0.01"
                                                class="form-control"
                                                id="{{ $parameter['Name'] }}"
                                                name="{{ $parameter['Name'] }}"
                                                required="{{ !$parameter['Nullable'] && !$parameter['AllowBlank'] ? 'true' : 'false' }}"
                                                value="{{ !empty($parameter['DefaultValues']) ? $parameter['DefaultValues'][0] : '' }}"
                                            >
                                            @if($parameter['ErrorMessage'])
                                                <div
                                                    class="form-text text-danger">{{ $parameter['ErrorMessage'] }}</div>
                                            @endif
                                        </div>
                                    @elseif($parameter['ParameterType'] === 'Integer')
                                        <div class="col-md-4 col-4 mb-3">
                                            <label for="{{ $parameter['Name'] }}"
                                                   class="form-label">{{ $parameter['Prompt'] }} {!! (!$parameter['Nullable'] && !$parameter['AllowBlank']) ? '<span class="text-danger" title="required">*</span>' : '' !!}</label>
                                            <input
                                                type="number"
                                                step="1"
                                                class="form-control"
                                                id="{{ $parameter['Name'] }}"
                                                name="{{ $parameter['Name'] }}"
                                                required="{{ !$parameter['Nullable'] && !$parameter['AllowBlank'] ? 'true' : 'false' }}"
                                                value="{{ !empty($parameter['DefaultValues']) ? $parameter['DefaultValues'][0] : '' }}"
                                            >
                                            @if($parameter['ErrorMessage'])
                                                <div
                                                    class="form-text text-danger">{{ $parameter['ErrorMessage'] }}</div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="col-md-4 col-4 mb-3">
                                            Unknown parameter type
                                        </div>
                                    @endif

                                @endforeach
                            </div>
                        </div>
                        <div class="col-2 py-3">
                            <button type="submit" class="btn btn-primary w-100" id="filterReportBtn">View Report
                            </button>
                        </div>
                    </div>

                </form>
            @endif
            <div class="card" id="reportMainBody" style="min-height: 50vh">
                <div class="py-4 text-center"><h3>....</h3></div>
            </div>
        </div>
    </div>

@endsection
@section('scripts')
    <script src="{{ asset('assets/libs/select2/js/select2.full.min.js') }}"></script>
    <script> const FilterButton = $("#filterReportBtn"), ReportContent = $("#reportMainBody");
        $(function () {
            @if(count($parameters)===0)
            getReport({});
            @else
            $('form#reportParametersForm').submit(async function (e) {
                e.preventDefault();
                FilterButton.prop('disable', true).addClass('disabled').prop('type', 'button').html('<i class="fas fa-spinner fa-spin"></i> please wait');
                let params = {};
                $('#reportParametersForm input, #reportParametersForm select').each(function () {
                    params[$(this).attr('name')] = $(this).val();
                });

                await getReport(params);
                FilterButton.prop('disable', false).removeClass('disabled').prop('type', 'submit').html('View Report');
            });
            $('.select-option').select2({
                width: '100%'
            });
            @endif
        });

        async function submitReportParameters() {

        }

        async function getReport(params) {
            ReportContent.html(' <div class="my-4 text-center"><i class="fas fa-spinner fa-spin fa-5x"></i><h3>Please wait</h3></div>');
            await $.get(getDocumentUrl(), params, function (data) {
                ReportContent.html(data);
            }).fail(function (jqXHR) {
                nError(jqXHR.responseJSON.message);
                ReportContent.html("<div class='my-4 text-center'><h3>" + jqXHR.responseJSON.message + "</h3></div>")
            });
        }

        /*  function getReport() {



              $.get(getDocumentUrl(), function (data) {
                  $("#reportMainBody").html(data);
              }).fail(function (jqXHR) {
                  nError(jqXHR.responseJSON.message);
                  $("#reportMainBody").html("<div class='my-4 text-center'><h3>" + jqXHR.responseJSON.message + "</h3></div>")
              });
          }*/

        /*$(function () {

            window.onload = loadReport;
        });
        function loadReport() {
            document.getElementById("reportIframe").src = "&StartDate=2025-01-01&EndDate=2025-01-31";
        }*/

    </script>
@endsection
