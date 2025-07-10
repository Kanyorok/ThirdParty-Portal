@extends('layouts.app')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="#">DMS</a></li>
    @yield('breadcrumb')
@endsection
@section('search-form')
    <style>
        .tt-menu {
            width: 100% !important;
            padding: .5rem 1.5rem !important;
            opacity: 0.98;
        }
    </style>
    <div class="form-search" action="" method="get">
        <i class="search-icon">
            <svg class="pc-icon">
                <use xlink:href="#custom-search-normal-1"></use>
            </svg>
        </i>
        <input type="search" name="q" class="form-control typeahead" id="SearchInput" style="width: 50vw;"
               placeholder="Search Files & Folders" data-url="{{ route('dms.search') }}">
    </div>
@endsection
@section('script')
    <script src="{{ asset('assets/libs/typeahead/typeahead.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/search/dms.min.js') }}"></script>
@endsection
