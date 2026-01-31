<?php

namespace App\Services\Finance;

use App\Models\Finance\FinanceInvoice;
use App\Models\Finance\FinanceInvoiceLine;
use App\Models\Finance\FinanceTaxRuleConfiguration;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class InvoiceIntakeService
{
    /**
     * Intakes an invoice and its lines into Finance.
     * Returns only RequestID + message.
     *
     * @param array $data
     * @param bool $enforceHeaderTotalMatch
     * @return array
     * @throws ValidationException
     */
    public function intake(array $data, bool $enforceHeaderTotalMatch = false): array
    {
        $v = Validator::make($data, [
            // header
            'IdempotencyKey' => ['nullable', 'string', 'max:100'],
            'SourceTable' => ['required', 'string', 'max:255'],
            'ModuleID' => ['nullable', 'integer'],
            'CurrencyID' => ['nullable', 'integer'],
            'CustomerID' => ['nullable', 'integer'],
            'InvoiceID' => ['nullable'],
            'InvoiceNumber' => ['nullable', 'string', 'max:100'],
            'InvoiceTitle' => ['required', 'string', 'max:100'],
            'InvoiceDate' => ['required', 'date'],
            'DueDate' => ['nullable', 'date', 'after_or_equal:InvoiceDate'],
            'InvoiceRemarks' => ['nullable', 'string', 'max:100'],
            'TaxID' => ['nullable', 'integer', Rule::exists('t_FinanceTaxRuleConfiguration', 'Id')],
            'TaxAmount' => ['nullable', 'numeric'],
            'TaxPercentage' => ['nullable', 'numeric'],
            'InvoiceAmount' => ['nullable', 'numeric'],
            'TotalAmount' => ['required', 'integer'],
            'AmountPaid' => ['nullable', 'integer'],
            'IsPaid' => ['nullable', 'boolean'],
            'IsGenerated' => ['nullable', 'boolean'],
            'Status' => ['nullable', 'in:draft,queued,approved,posted,rejected'],
            'ApprovalStatus' => ['nullable', 'in:draft,posted,rejected'],
            'ApprovalReason' => ['nullable', 'string'],

            // audit
            'CreatedBy' => ['required', 'integer'],
            'ModifiedBy' => ['required', 'integer'],

            // lines
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.InvoiceLineName' => ['required', 'string', 'max:150'],
            'lines.*.Description' => ['nullable', 'string', 'max:250'],
            'lines.*.UnitCost' => ['required', 'integer'],
            'lines.*.Quantity' => ['required', 'integer', 'min:1'],
            'lines.*.Tax' => ['nullable', 'string', 'max:10'],
            'lines.*.TaxID' => ['nullable', 'string', 'max:10'],
            'lines.*.TaxAmount' => ['nullable', 'string', 'max:10'],
            'lines.*.CurrencyID' => ['nullable', 'integer'],
            'lines.*.Discount' => ['nullable', 'integer'],
            'lines.*.Total' => ['required', 'integer'],
        ]);

        if ($v->fails()) {
            throw new ValidationException($v);
        }

        $payload = $v->validated();

        // Compute IdempotencyKey if not provided
        $idk = $payload['IdempotencyKey'] ?? $this->computeIdempotencyKey($payload);

        $lineSubtotal = array_sum(array_map(fn ($l) => (int)$l['Total'], $payload['lines']));

        $invoiceAmount = $payload['InvoiceAmount'] ?? (float)$lineSubtotal;
        $taxAmount = $payload['TaxAmount'] ?? 0.0;
        $taxPercentage = $payload['TaxPercentage'] ?? null;

        if (! empty($payload['TaxID'])) {
            $taxConfig = FinanceTaxRuleConfiguration::find($payload['TaxID']);
            if ($taxConfig) {
                $taxPercentage = (float)$taxConfig->Rate;
                if (! array_key_exists('TaxAmount', $payload)) {
                    $taxAmount = round($invoiceAmount * ($taxPercentage / 100), 2);
                }
                if (! array_key_exists('InvoiceAmount', $payload)) {
                    $invoiceAmount = $lineSubtotal + $taxAmount;
                }
            }
        }

        $effectiveTotal = $invoiceAmount - $taxAmount;

        if ($enforceHeaderTotalMatch) {
            if ((int)$payload['TotalAmount'] !== (int)$lineSubtotal) {
                return [
                    'message' => "Header TotalAmount does not match sum of lines.",
                    'request_id' => null,
                ];
            }
        }

        $invoice = DB::transaction(function () use ($payload, $idk, $invoiceAmount, $taxAmount, $taxPercentage, $effectiveTotal) {
            $now = Carbon::now();

            $header = [
                'IdempotencyKey' => $idk,
                'SourceTable' => $payload['SourceTable'],

                'ModuleID' => $payload['ModuleID'] ?? null,
                'CurrencyID' => $payload['CurrencyID'] ?? null,
                'CustomerID' => $payload['CustomerID'] ?? null,
                'InvoiceID' => $payload['InvoiceID'] ?? null,
                'InvoiceNumber' => $payload['InvoiceNumber'] ?? null,
                'InvoiceTitle' => $payload['InvoiceTitle'],
                'InvoiceDate' => $payload['InvoiceDate'],
                'DueDate' => $payload['DueDate'] ?? null,
                'InvoiceRemarks' => $payload['InvoiceRemarks'] ?? null,
                'TotalAmount' => round($invoiceAmount, 2),
                'TaxAmount' => round($taxAmount, 2),
                'InvoiceAmount' => (int)round($effectiveTotal),
                'TaxPercentage' => $taxPercentage !== null ? round($taxPercentage, 4) : null,
                'TaxID' => $payload['TaxID'] ?? null,
                'AmountPaid' => (int)($payload['AmountPaid'] ?? 0),
                'IsPaid' => (bool)($payload['IsPaid'] ?? false),
                'IsGenerated' => (bool)($payload['IsGenerated'] ?? true),
                'Status' => $payload['Status'] ?? 'draft',
                'ApprovalStatus' => $payload['ApprovalStatus'] ?? 'draft',
                'ApprovalReason' => $payload['ApprovalReason'] ?? null,

                'ModifiedBy' => $payload['ModifiedBy'],
                'ModifiedOn' => $now,
            ];

            // check if invoice exists (handles double submits)
            $invoice = FinanceInvoice::where('IdempotencyKey', $idk)->first();

            if (! $invoice) {
                $invoice = new FinanceInvoice();
                $invoice->fill(array_merge($header, [
                    'CreatedBy' => $payload['CreatedBy'],
                    'CreatedOn' => $now,
                ]));
                $invoice->save();

                // insert lines
                foreach ($payload['lines'] as $line) {
                    $l = new FinanceInvoiceLine();
                    $l->InvoiceID = $invoice->Id;
                    $l->InvoiceLineName = $line['InvoiceLineName'];
                    $l->Description = $line['Description'] ?? null;
                    $l->UnitCost = (int)$line['UnitCost'];
                    $l->Quantity = (int)$line['Quantity'];
                    $l->Tax = $line['Tax'] ?? null;
                    $l->TaxID = $line['TaxID'] ?? null;
                    $l->TaxAmount = $line['TaxAmount'] ?? '0';
                    $l->CurrencyID = $line['CurrencyID'] ?? ($payload['CurrencyID'] ?? null);
                    $l->Discount = (int)($line['Discount'] ?? 0);
                    $l->Total = (int)$line['Total'];
                    $l->CreatedBy = $payload['CreatedBy'];
                    $l->CreatedOn = $now;
                    $l->ModifiedBy = $payload['ModifiedBy'];
                    $l->ModifiedOn = $now;
                    $l->save();
                }
            }

            return $invoice;
        });

        activity('finance.invoice.intake')
            ->causedBy($payload['CreatedBy'])
            ->performedOn($invoice)
            ->log('Invoice intake Finance');


        return [
            'message' => 'Invoice successfully stored in Finance.',
            'request_id' => $invoice->RequestID,
        ];
    }

    protected function computeIdempotencyKey(array $payload): string
    {
        $parts = [
            'SourceTable' => $payload['SourceTable'] ?? null,
            'InvoiceID' => $payload['InvoiceID'] ?? null,
            'CustomerID' => $payload['CustomerID'] ?? null,
            'InvoiceDate' => $payload['InvoiceDate'] ?? null,
            'InvoiceTitle' => $payload['InvoiceTitle'] ?? null,
        ];

        $norm = array_map(fn ($v) => is_string($v) ? trim(mb_strtoupper($v)) : $v, $parts);

        return hash('sha256', json_encode($norm));
    }
}
