<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TenderInvitationSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('t_TenderInvitations')->insert([
            [
                'TenderId' => 10, // Assumes tender ID 1 exists in t_Tenders
                'SupplierId' => 2, // Assumes supplier ID 1 exists in t_Suppliers
                'InvitationDate' => Carbon::today()->subDays(5),
                'ResponseStatus' => 'Accepted',
                'ResponseDate' => Carbon::today()->subDays(3),
                'DeclineReason' => null,
                'ConfirmationAttachment' => 'attachment_001.pdf',
                'CreatedBy' => 1, // Assumes user ID 1 exists in t_Users
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ],
            [
                'TenderId' => 11, // Assumes tender ID 2 exists in t_Tenders
                'SupplierId' => 3, // Assumes supplier ID 2 exists in t_Suppliers
                'InvitationDate' => Carbon::today()->subDays(7),
                'ResponseStatus' => 'Declined',
                'ResponseDate' => Carbon::today()->subDays(4),
                'DeclineReason' => 'Unable to meet project timeline',
                'ConfirmationAttachment' => null,
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ],
            [
                'TenderId' => 12, // Assumes tender ID 3 exists in t_Tenders
                'SupplierId' => 4, // Assumes supplier ID 3 exists in t_Suppliers
                'InvitationDate' => Carbon::today()->subDays(2),
                'ResponseStatus' => 'Pending',
                'ResponseDate' => null,
                'DeclineReason' => null,
                'ConfirmationAttachment' => null,
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
