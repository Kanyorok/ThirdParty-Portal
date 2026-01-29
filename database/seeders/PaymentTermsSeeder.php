<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentTermsSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('🏦 Seeding Payment Terms...');

        // Check if payment terms exist
        $existingTerms = DB::table('t_CodeDetails')
            ->where('CodeID', 'PaymentTerm')
            ->count();

        if ($existingTerms < 10) {
            $paymentTerms = [
                [
                    'ID' => 200,
                    'CodeID' => 'PaymentTerm',
                    'Value' => 'COD',
                    'Description' => 'Cash on Delivery - Payment upon receipt of goods',
                    'DisplayOrder' => 1,
                    'IsActive' => 1,
                    'CreatedBy' => 1,
                    'CreatedOn' => now(),
                    'ModifiedBy' => 1,
                    'ModifiedOn' => now(),
                ],
                [
                    'ID' => 201,
                    'CodeID' => 'PaymentTerm',
                    'Value' => 'NET15',
                    'Description' => 'Net 15 - Payment due within 15 days',
                    'DisplayOrder' => 2,
                    'IsActive' => 1,
                    'CreatedBy' => 1,
                    'CreatedOn' => now(),
                    'ModifiedBy' => 1,
                    'ModifiedOn' => now(),
                ],
                [
                    'ID' => 202,
                    'CodeID' => 'PaymentTerm',
                    'Value' => 'NET30',
                    'Description' => 'Net 30 - Payment due within 30 days',
                    'DisplayOrder' => 3,
                    'IsActive' => 1,
                    'CreatedBy' => 1,
                    'CreatedOn' => now(),
                    'ModifiedBy' => 1,
                    'ModifiedOn' => now(),
                ],
                [
                    'ID' => 203,
                    'CodeID' => 'PaymentTerm',
                    'Value' => 'NET45',
                    'Description' => 'Net 45 - Payment due within 45 days',
                    'DisplayOrder' => 4,
                    'IsActive' => 1,
                    'CreatedBy' => 1,
                    'CreatedOn' => now(),
                    'ModifiedBy' => 1,
                    'ModifiedOn' => now(),
                ],
                [
                    'ID' => 204,
                    'CodeID' => 'PaymentTerm',
                    'Value' => 'NET60',
                    'Description' => 'Net 60 - Payment due within 60 days',
                    'DisplayOrder' => 5,
                    'IsActive' => 1,
                    'CreatedBy' => 1,
                    'CreatedOn' => now(),
                    'ModifiedBy' => 1,
                    'ModifiedOn' => now(),
                ],
                [
                    'ID' => 205,
                    'CodeID' => 'PaymentTerm',
                    'Value' => '2/10NET30',
                    'Description' => '2/10 Net 30 - 2% discount if paid within 10 days, otherwise net 30',
                    'DisplayOrder' => 6,
                    'IsActive' => 1,
                    'CreatedBy' => 1,
                    'CreatedOn' => now(),
                    'ModifiedBy' => 1,
                    'ModifiedOn' => now(),
                ],
                [
                    'ID' => 206,
                    'CodeID' => 'PaymentTerm',
                    'Value' => 'ADVANCE',
                    'Description' => 'Advance Payment - 100% payment before delivery',
                    'DisplayOrder' => 7,
                    'IsActive' => 1,
                    'CreatedBy' => 1,
                    'CreatedOn' => now(),
                    'ModifiedBy' => 1,
                    'ModifiedOn' => now(),
                ],
                [
                    'ID' => 207,
                    'CodeID' => 'PaymentTerm',
                    'Value' => '50ADVANCE',
                    'Description' => '50% Advance - 50% advance, 50% on delivery',
                    'DisplayOrder' => 8,
                    'IsActive' => 1,
                    'CreatedBy' => 1,
                    'CreatedOn' => now(),
                    'ModifiedBy' => 1,
                    'ModifiedOn' => now(),
                ],
                [
                    'ID' => 208,
                    'CodeID' => 'PaymentTerm',
                    'Value' => 'MILESTONE',
                    'Description' => 'Milestone Payments - Payments based on delivery milestones',
                    'DisplayOrder' => 9,
                    'IsActive' => 1,
                    'CreatedBy' => 1,
                    'CreatedOn' => now(),
                    'ModifiedBy' => 1,
                    'ModifiedOn' => now(),
                ],
                [
                    'ID' => 209,
                    'CodeID' => 'PaymentTerm',
                    'Value' => 'LC',
                    'Description' => 'Letter of Credit - Payment via bank letter of credit',
                    'DisplayOrder' => 10,
                    'IsActive' => 1,
                    'CreatedBy' => 1,
                    'CreatedOn' => now(),
                    'ModifiedBy' => 1,
                    'ModifiedOn' => now(),
                ],
            ];

            foreach ($paymentTerms as $term) {
                $exists = DB::table('t_CodeDetails')->where('ID', $term['ID'])->exists();
                if (! $exists) {
                    DB::table('t_CodeDetails')->insert($term);
                    $this->command->info("  ✅ Added: {$term['Description']}");
                }
            }
        }

        $this->command->info('✅ Payment Terms seeding completed!');
    }
}
