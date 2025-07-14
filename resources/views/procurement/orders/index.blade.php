@php use Carbon\Carbon; @endphp
@extends('layouts.app')
@section('title', 'Purchase Orders')
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <style>
        .select2-container {
            width: 100% !important;
        }
    </style>
@endsection
@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-body">
                    <table id="ordersTable"
                           class="table table-striped dataTable no-footer dtr-inline w-100 table-responsive">
                        <thead>

                        <tr>
                            <th>#</th>
                            <th>Order No</th>
                            <th>Order Date</th>
                            <th>RFQ No</th>
                            <th>Priority</th>
                            <th>Branch</th>
                            <th>Order Amount</th>
                            <th>Order Lines</th>
                            <th>Created By</th>
                            <th>Created On</th>
                            <th>Action</th>
                        </tr>

                        </thead>
                        <tbody>
                        @forelse($details as $item)
                            <tr>
                                <td>{{  $loop->iteration }}</td>
                                <td>{{ $item->OrderNo }}</td>
                                <td>{{ Carbon::parse($item->OrderDate)->format('d/m/Y')}}</td>
                                <td>{{
                                        echo e( $item->Ex}}</td>
                                                                        <td>{{
                                        echo e( $item->P}}</td>
                                                                        <td>{{
                                        echo e( $item->B}}</td>
                                                                        <td>{{
                                        echo e( number_format($item->UnitPr}}</td>
                                                                        <td>{{
                                        echo e( $item->ord}}</td>
                                                                        <td>{{
                                        echo e( $item->Cr}}</td>
                                                                        <td>{{
                                        echo e( Carbon::parse($item->CreatedOn)->}}</td>
                                                                        <td><a href="{{ ?><?php
                                        echo e( route('purchaseOrder.sh}}" class="btn btn-info btn-sm">View</a>
                                                                            <a href="{{ ?><?php
                                        echo e( route('purchaseOrder.approv}}"
                                       class="btn btn-success btn-sm">Approve</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="15" class="text-center">No orders found.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection
@section(v->startS)
    <script src="{{ ?><?php
echo e( asset('assets/plugins/select2/js/select}}"></script>
    <script src="{{ ?><?php
echo e( asset('assets/js/}}"></script>

@endsection
