<?php

namespace Database\Seeders;

use App\Enums\LocalityTypeEnum;
use App\Helpers\SystemHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class LocalitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = SystemHelper::user();
        $counties = collect();
        $cities = collect();
        $date = now();
        $response = Http::get('https://gist.githubusercontent.com/danielmaangi/2c97392df1473f859328f6be070b13cb/raw/bc541a2129c6a75e28a9f1208b687f37dcd40df2/kenyan_counties.json')->collect()->sortBy('code');


        foreach ($response as $res) {
            $counties->add([
                //'ID' => $res['code'],
                            'Name'         => $res['name'],
                            'LocationType' => LocalityTypeEnum::County->value,
                            'CreatedOn'    => $date,
                            'CreatedBy'    => $user->Id,
                            'ModifiedOn'   => $date,
                            'ModifiedBy'   => $user->Id,
                           ]);
            $cities->add([
                          'Name'         => (array_key_exists('capital', $res)) ? $res['capital'] : $res['name'],
                          'LocationType' => LocalityTypeEnum::City->value,
                          'LocalityID'   => $res['code'],
                          'CreatedOn'    => $date,
                          'CreatedBy'    => $user->Id,
                          'ModifiedOn'   => $date,
                          'ModifiedBy'   => $user->Id,
                         ]);
        }


        DB::table('t_Localities')->insert($counties->toArray());


        DB::table('t_Localities')->insert($cities->toArray());
    }
}
