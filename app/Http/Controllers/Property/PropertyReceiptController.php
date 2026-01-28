<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Services\Property\BillingAndReceipting\PropertyReceiptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PropertyReceiptController extends Controller
{
    public function index(Request $request)
    {
        $receipts = collect(DB::select(
            'EXEC p_GetRentInvoiceReceipts ?',
            ['Posted']
        ));

        // 🔍 Filters
        if ($request->filled('invoice_number')) {
            $receipts = $receipts->filter(
                fn ($r) =>
                str_contains(
                    strtolower($r->InvoiceNumber),
                    strtolower($request->invoice_number)
                )
            );
        }

        if ($request->filled('lease')) {
            $receipts = $receipts->filter(
                fn ($r) =>
                str_contains(
                    strtolower($r->Lease),
                    strtolower($request->lease)
                )
            );
        }

        return view(
            'property.billingandreceipting.receipting.index',
            compact('receipts')
        );
    }












    //         try {

    //             // Create the receipt
    //             PropertyReceiptService::create(
    //                 Auth::user()

    //             // --- Update Invoice Status ---




    //     <!DOCTYPE html>
    //     <html lang='en'>
    //     <head>
    //         <meta charset='UTF-8'>
    //         <title>Tenant Payment Receipt</title>
    //         <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    //         <style>
    //             body {
    //                 background: linear-gradient(135deg, #e3f2fd 0%, #f8f9fa 100%);
    //                 font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    //                 padding: 60px 20px;
    //                 color: #212529;

    //             .receipt-container {
    //                 background: #fff;
    //                 max-width: 800px;
    //                 margin: auto;
    //                 border-radius: 18px;
    //                 box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
    //                 overflow: hidden;
    //                 position: relative;
    //                 animation: fadeIn 0.6s ease-in-out;

    //             @keyframes fadeIn {
    //                 from { opacity: 0; transform: translateY(20px); }
    //                 to { opacity: 1; transform: translateY(0); }

    //             .receipt-header {
    //                 background: linear-gradient(90deg, #0d6efd, #004aad);
    //                 color: #fff;
    //                 padding: 25px 35px;
    //                 display: flex;
    //                 align-items: center;
    //                 justify-content: space-between;

    //             .receipt-header h4 {
    //                 margin: 0;
    //                 font-weight: 700;
    //                 font-size: 1.4rem;

    //             .receipt-body {
    //                 padding: 35px;

    //             .section-title {
    //                 color: #0d6efd;
    //                 font-weight: 600;
    //                 border-left: 4px solid #0d6efd;
    //                 padding-left: 10px;
    //                 margin-bottom: 15px;
    //                 font-size: 1rem;

    //             .details p {
    //                 font-size: 15px;
    //                 margin-bottom: 6px;

    //             table {
    //                 width: 100%;
    //                 border-collapse: collapse;
    //                 margin-top: 20px;
    //                 border-radius: 10px;
    //                 overflow: hidden;

    //             th {
    //                 background: #004aad;
    //                 color: #fff;
    //                 text-align: left;
    //                 padding: 10px;

    //             td {
    //                 padding: 10px;
    //                 border-bottom: 1px solid #dee2e6;

    //             tr:last-child td {
    //                 border-bottom: none;

    //             .amount-box {
    //                 text-align: center;
    //                 background: linear-gradient(145deg, #f3f9ff, #ffffff);
    //                 border: 1px solid #e3f2fd;
    //                 border-radius: 14px;
    //                 padding: 30px;
    //                 margin: 40px 0;

    //             .amount-box h2 {
    //                 color: #004aad;
    //                 font-weight: 800;
    //                 font-size: 2.2rem;

    //             .footer-note {
    //                 text-align: center;
    //                 font-size: 0.9rem;
    //                 color: #6c757d;
    //                 border-top: 1px solid #dee2e6;
    //                 padding-top: 15px;
    //                 margin-top: 30px;

    //             .btn-print {
    //                 position: absolute;
    //                 top: 20px;
    //                 right: 20px;
    //                 border-radius: 8px;
    //                 z-index: 100;

    //             @media print {
    //                 @page { size: A4; margin: 20mm; }
    //                 body { background: #fff !important; padding: 0; }
    //                 .receipt-container { border: 1px solid #ddd; box-shadow: none; border-radius: 0; }
    //                 .receipt-header { background: #004aad !important; -webkit-print-color-adjust: exact; }
    //                 .section-title { color: #004aad !important; border-color: #004aad !important; -webkit-print-color-adjust: exact; }
    //                 .amount-box { background: #eef5ff !important; border-color: #aac8ff !important; -webkit-print-color-adjust: exact; }
    //                 .btn-print { display: none !important; }
    //         </style>
    //     </head>

    //     <body>
    //         <div class='receipt-container'>
    //             <button onclick='window.print()' class='btn btn-primary btn-sm btn-print'>
    //                 🖨️ Print Receipt
    //             </button>

    //             <div class='receipt-header'>
    //                 <div>
    //                     <h4>🏠 Tenant Payment Receipt</h4>
    //                     <small class='text-light'>Generated by " . config('app.name') . "</small>
    //                 </div>
    //             </div>

    //             <div class='receipt-body'>
    //                 <div class='row details mb-4'>
    //                     <div class='col-md-6'>
    //                         <div class='section-title'>Tenant Information</div>
    //                         <p><strong>Tenant:</strong> {$tenant}</p>
    //                         <p><strong>Receipt No:</strong> {$receipt->ReferenceNo}</p>
    //                         <p><strong>Invoice No:</strong> {$invoiceNo}</p>
    //                     </div>
    //                     <div class='col-md-6'>
    //                         <div class='section-title'>Transaction Info</div>
    //                         <p><strong>Payment Date:</strong> {$receipt->PaymentDate}</p>
    //                         <p><strong>Payment Method:</strong> {$receipt->paymentmethod->Description}</p>
    //                         <p><strong>Status:</strong> {$status}</p>
    //                     </div>
    //                 </div>

    //                 <table class='table table-bordered'>
    //                     <thead>
    //                         <tr>
    //                             <th>Description</th>
    //                             <th>Amount (KES)</th>
    //                         </tr>
    //                     </thead>
    //                     <tbody>
    //                         <tr><td>Rent</td><td>" . number_format($receipt->RentAmount, 2) . "</td></tr>
    //                         <tr><td>Service Charge</td><td>" . number_format($receipt->ServicesCharge, 2) . "</td></tr>
    //                         <tr><td>Other Charges</td><td>" . number_format($receipt->OtherCharges, 2) . "</td></tr>
    //                         <tr><td><strong>Total Due</strong></td><td><strong>" . number_format($receipt->TotalDue, 2) . "</strong></td></tr>
    //                         <tr><td><strong>Balance</strong></td><td><strong>" . number_format($receipt->Balance, 2) . "</strong></td></tr>
    //                         <tr><td><strong>Paid Now</strong></td><td><strong>" . number_format($receipt->AmountPaidNow, 2) . "</strong></td></tr>
    //                     </tbody>
    //                 </table>

    //                 <div class='amount-box'>
    //                     <h2>KES " . number_format($receipt->AmountPaidNow, 2) . "</h2>
    //                     <p>Amount Paid</p>
    //                 </div>

    //                 <p><strong>Remarks:</strong> {$receipt->Remarks}</p>

    //                 <div class='footer-note'>
    //                     Thank you for your payment.<br>
    //                     This receipt is system-generated and requires no signature.
    //                 </div>
    //             </div>
    //         </div>
    //     </body>
    //     </html>";




    //         try {

    //             // Log the error for debugging
    //             Log::error('Error deleting Rent Receipt: ' . $th->getMessage());
}
