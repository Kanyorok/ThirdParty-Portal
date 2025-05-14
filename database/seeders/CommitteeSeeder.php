<?php

namespace Database\Seeders;

use App\Helpers\SystemHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommitteeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $date = now();
        $user = SystemHelper::user();

        DB::table('t_Committees')->insert([
                                           [
                                            "CommitteeID" => "Comm-001",
                                            "Name"        => "Full Board",
                                            'Notes'       => "All Board Members",
                                            'CreatedOn'   => $date,
                                            'CreatedBy'   => $user->Id,
                                            'ModifiedOn'  => $date,
                                            'ModifiedBy'  => $user->Id,
                                           ],
                                           [
                                            "CommitteeID" => "Comm-002",
                                            "Name"        => "Credit Committee",
                                            'Notes'       => "",
                                            'CreatedOn'   => $date,
                                            'CreatedBy'   => $user->Id,
                                            'ModifiedOn'  => $date,
                                            'ModifiedBy'  => $user->Id,
                                           ],
                                          ]);
    }
}
