@extends('layouts.app')
@section('title', 'Payment Vouchers')
 
@section('content')
<div class="container">
    <div class="card shadow p-4 rounded-4">
        <div class="card-header bg-light py-1 px-3">
            <h6 class="mb-0 text-muted"id="noteTypeTitle"><i class="fab fa-wpforms text-info"></i> Vouchers List</h6>
        </div>
        {{-- <h4 class="mb-4">📋 Payment Vouchers List</h4> --}}

        <div class="card-body">
            <p class="text-muted">Below is the list of all Vouchers with their details.</p>
            <div class="mb-2 d-flex justify-content-between">
                <a href="{{ route('paymentvoucher.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Voucher</a>
            </div>
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Voucher No</th>
                        <th>Invoice Ref</th>
                        <th>Payment Method</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Payment Type</th>
                        <th>Description</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @if($vouchers->count())
                    @forEach( $vouchers as $item)
                    <tr>
                        <td>{{ $loop->iteration}}</td>
                        <td>{{ $item->VoucherNo ?? '-'}}</td>
                        <td>{{ $item->invoice->InvoiceNumber  ?? '-'}}</td>
                        <td class="text-centre">{{ $item->PaymentMethod ?? '-'}}</td>
                        <td class="text-end">{{ number_format($item->TotAmnt, 2)  ?? '-'}}</td>
                        <td>{{ $item->Status ?? '-'}}</td>
                        <td>{{ $item->PaymentType ?? '-'}}</td>
                        <td title="{{ $item->Description ?? '-'}}" style="white-space: nowrap;">{{ $item->Description ?? '-'}}</td>
                        <td>
                            <a href="#" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i></a>
                            <a href="#" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                            <button type="button"
                                class="btn btn-sm btn-danger custom-delete-btn"
                                {{-- data-bs-toggle="modal"
                                data-bs-target="#customDeleteConfirmModal"
                                data-name="{{$item->taxType->TaxTypeName}}"    {{-- Pass item name --}}
                                {{-- data-route="{{ route('taxruleconfig.destroy', $item->Id) }}"> Pass delete route --}} >
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                    @else
                    <tr>
                        <td colspan="7" class="text-center">
                            <div class="text-center">
                                No Invoice Entries found
                            </div>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection