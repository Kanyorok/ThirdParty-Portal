<?php

namespace Database\Seeders;

use App\Models\ThirdParty\ThirdPartyCategory;
use Illuminate\Database\Seeder;

class ThirdPartyCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ThirdPartyCategory::create([
            'ThirdPartyId' => 1,
            'CategoryID' => 5,
            'CreatedBy' => 1,
            'ModifiedBy' => 1,
            'CreatedOn' => now(),
        ]);
    }
}
