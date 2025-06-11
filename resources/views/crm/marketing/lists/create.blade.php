@php use App\Enums\Core\ComparisonOperatorsEnum; @endphp
@php use App\Models\BR\Client; @endphp
@php use App\Models\CRM\Lead; @endphp
@extends('layouts.app')

@section('title','Create Dynamic Lists')
@section('styles')

@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">@yield('title')</h3>
                </div>
                <form action="{{ route('marketing-list.store') }}" method="post" class="card-body row"
                      id="createListForm">
                    @csrf
                    <div class="mb-3 col-md-6 ">
                        <label class="form-label" for="Label">Label <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="Label" name="Label" required
                               placeholder="Label">
                        <p id="Label_error" class="invalid-feedback d-none error col-12" role="alert"></p>

                        <label class="form-label" for="Party">Party <span class="text-danger">*</span></label>
                        <select class="form-control" name="Party" id="Party" required>
                            <option disabled selected>Select a Party</option>
                            <option value="{{ Client::getPrimaryKey() }}">Members</option>
                            <option value="{{ Lead::getPrimaryKey() }}">Leads</option>
                        </select>
                        <p id="Party_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label" for="Notes">Notes </label>
                        <textarea name="Notes" id="Notes" rows="4" class="form-control"
                                  maxlength="1000"></textarea>
                        <p id="Notes_error" class="invalid-feedback d-none error col-12"
                           role="alert"></p>
                    </div>
                    <div class="clearfix"></div>
                    <h3 class="mb-1 col-12">Filters</h3> {{--CreatedOn CreatedOnType   --}}
                    <hr class="col-12 pt-0 mt-0">
                    <div class="col-md-4 col-sm-6 col-12 mb-3">
                        <div class="row">
                            <label class="form-label col-12" for="CreatedOn">Created On </label>
                            <div class="col-4">
                                <select class="form-control" name="CreatedOnType" id="CreatedOnType">
                                    @foreach(ComparisonOperatorsEnum::getAll() as $comparison)
                                        <option value="{{ $comparison->value }}">{{ $comparison->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-8">
                                <input type="text" class="form-control flatpickr-datetime" id="CreatedOn"
                                       name="CreatedOn" placeholder="YYYY-MM-DD eg 2024-01-30">
                            </div>
                        </div>
                        <p id="CreatedOn_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                    </div>
                    <div class="col-md-4 col-sm-6 col-12 mb-3 d-none client-fields">
                        <div class="row">
                            <label class="form-label" for="DateOfBirth">Date of Birth </label>
                            <div class="col-4">
                                <select class="form-control" name="DateOfBirthType" id="DateOfBirthType">
                                    @foreach(ComparisonOperatorsEnum::getAll() as $comparison)
                                        <option value="{{ $comparison->value }}">{{ $comparison->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-8">
                                <input type="text" class="form-control flatpickr-datetime" id="DateOfBirth"
                                       name="DateOfBirth" placeholder="YYYY-MM-DD eg 2024-01-30">
                            </div>
                        </div>
                        <p id="DateOfBirth_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                    </div>
                    <div class="col-md-4 col-sm-6 col-12 mb-3 d-none client-fields">
                        <div class="row">
                            <label class="form-label" for="MemberType">Member Type </label>
                            <div class="col-4">
                                <select class="form-control" readonly="">
                                    <option
                                        selected>{{ ComparisonOperatorsEnum::EqualTo->name }}</option>
                                </select>
                            </div>
                            <div class="col-8">
                                <select class="form-control" name="MemberType" id="MemberType">
                                    <option disabled selected>Select a Member Type</option>
                                    @foreach($MemberTypes as $MemberType)
                                        <option
                                            value="{{ $MemberType->SubCodeID }}">{{ $MemberType->Description }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <p id="MemberType_error" class="invalid-feedback d-none error col-12" role="alert"></p>
                    </div>

                    <div class="mt-2">
                        <hr>
                        <button type="button" class="btn btn-secondary float-start"
                                data-bs-dismiss="modal">
                            cancel
                        </button>
                        <button class="btn btn-primary float-end" id="createListBtn" type="submit"><i
                                class="fas fa-save"></i> add a dynamic list
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src='{{ asset('assets/libs/moment/moment-with-locales.js') }}'></script>
    <script>
        $(function () {
            $('#Party').on('change', function () {
                if (this.value === '{{ Client::getPrimaryKey() }}') {
                    $('.leads-fields').addClass('d-none');
                    $('.client-fields').removeClass('d-none');
                } else if (this.value === '{{ Lead::getPrimaryKey() }}') {
                    $('.leads-fields').removeClass('d-none');
                    $('.client-fields').addClass('d-none');
                }
            });

            flatpickr("#CreatedOn", {
                enableTime: false,
                altInput: true,
                allowInput: true,
                maxDate: moment().format('YYYY-MM-DD'),
                altFormat: "F j, Y",
                dateFormat: "Y-m-d",
            });
            flatpickr("#DateOfBirth", {
                enableTime: false,
                altInput: true,
                allowInput: true,
                maxDate: moment().format('YYYY-MM-DD'),
                altFormat: "F j, Y",
                dateFormat: "Y-m-d",
            });
        });
    </script>
@endsection
