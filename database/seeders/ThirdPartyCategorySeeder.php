<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ThirdParty\ThirdPartyCategory;

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
