<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenderSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $currencyId = DB::table('t_Currencies')->select('Id')->first()->Id; // Fetch the first available CurrencyId

        DB::table('t_Tenders')->insert([
            [
                'TenderNo' => 'TND-2025-001',
                'Title' => 'Supply of Office Laptops',
                'TenderType' => 'Open',
                'TenderCategory' => 'Goods',
                'ScopeOfWork' => 'Supply of 50 high-performance laptops for office use, including installation and configuration.',
                'Instructions' => 'Submit bids electronically via the procurement portal. Include warranty details and delivery timeline.',
                'SubmissionDeadline' => Carbon::today()->addDays(30),
                'OpeningDate' => Carbon::today()->addDays(31),
                'Status' => 'Published',
                'ProcurementModeId' => 1, // Assumes 'Open Tender' (Id = 1) exists in t_ProcurementModes
                'EstimatedValue' => 5000000.00,
                'CurrencyId' => $currencyId,
                'RelatedPRID' => 1, // Assumes a valid PR ID (e.g., 1) exists in t_PurchaseRequisitions
                'CreatedBy' => 1, // Assumes user ID 1 exists in t_Users
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ],
            [
                'TenderNo' => 'TND-2025-002',
                'Title' => 'Construction of Staff Quarters',
                'TenderType' => 'Restricted',
                'TenderCategory' => 'Works',
                'ScopeOfWork' => 'Construction of 20 staff housing units, including plumbing and electrical installations.',
                'Instructions' => 'Only prequalified contractors may submit bids. Provide detailed project timelines and safety plans.',
                'SubmissionDeadline' => Carbon::today()->addDays(45),
                'OpeningDate' => Carbon::today()->addDays(46),
                'Status' => 'Draft',
                'ProcurementModeId' => 2, // Assumes 'Restricted Tender' (Id = 2) exists in t_ProcurementModes
                'EstimatedValue' => 25000000.00,
                'CurrencyId' => $currencyId,
                'RelatedPRID' => 1, // Assumes a valid PR ID (e.g., 1) exists in t_PurchaseRequisitions
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ],
            [
                'TenderNo' => 'TND-2025-003',
                'Title' => 'Consultancy for IT System Upgrade',
                'TenderType' => 'Open',
                'TenderCategory' => 'Services',
                'ScopeOfWork' => 'Provide consultancy services for upgrading the organization’s IT infrastructure.',
                'Instructions' => 'Submit technical and financial proposals. Include CVs of key personnel.',
                'SubmissionDeadline' => Carbon::today()->addDays(20),
                'OpeningDate' => Carbon::today()->addDays(21),
                'Status' => 'Published',
                'ProcurementModeId' => 1, // Assumes 'Open Tender' (Id = 1) exists in t_ProcurementModes
                'EstimatedValue' => 3000000.00,
                'CurrencyId' => $currencyId,
                'RelatedPRID' => 1, // Assumes a valid PR ID (e.g., 1) exists in t_PurchaseRequisitions
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ],
        ]);
    }
}
