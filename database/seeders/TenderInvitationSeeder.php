<?php

namespace Database\Seeders;

use App\Models\TenderInvitation;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class TenderInvitationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        // Generate 50 random supplier names mapped to IDs
        $suppliers = [];
        for ($i = 1; $i <= 50; $i++) {
            $suppliers[$i] = $faker->unique()->company; // Unique company names
        }

        // Create 20 sample tender invitations
        for ($i = 0; $i < 20; $i++) {
            $supplierId = $faker->numberBetween(1, 50); // Select random SupplierID
            TenderInvitation::create([
                'TenderID' => $faker->numberBetween(1, 10), // Assuming 10 tenders exist
                'SupplierID' => $supplierId, // Numeric ID for schema
                'InvitationDate' => $faker->dateTimeBetween('-1 year', 'now'),
                'ResponseStatus' => $faker->randomElement([
                    TenderInvitation::STATUS_PENDING,
                    TenderInvitation::STATUS_ACCEPTED,
                    TenderInvitation::STATUS_DECLINED
                ]),
                'ResponseDate' => $faker->optional(0.7)->dateTimeBetween('-6 months', 'now'), // 70% chance of response
                'DeclineReason' => $faker->optional(0.3)->sentence(), // 30% chance of decline reason
                'ConfirmationAttachment' => $faker->optional(0.5)->filePath(), // 50% chance of attachment
            ]);
        }

        // Log supplier ID-to-name mapping for reference
        \Log::info('Supplier ID to Random Name Mapping:', $suppliers);
    }
}
