<?php

namespace Database\Seeders;

use App\Helpers\SystemHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use JsonException;
use Log;

class LocalitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $local = database_path('data/countries_states_cities.json');

        // Fetch and decode JSON data
        if (!file_exists($local)) {
            return;
        }

        try {
            $countries = json_decode(file_get_contents($local), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            Log::error('Could not fetch countries-states-cities-database data from ' . $local . '. Error: ' . $e->getMessage());
            return;
        }
        $actor = SystemHelper::user();
        $date = now();

        // Process each country
        foreach ($countries as $countryData) {

            $currencyId = $this->getCurrencyId($countryData['currency'], $countryData['currency_symbol']);
            if (!$currencyId) {
                $currencyId = DB::table('t_Currencies')->insertGetId([
                    "Name" => $countryData['currency_name'],
                    "Code" => $countryData['currency'],
                    "Symbol" => $countryData['currency_symbol'],
                    "SymbolNative" => $countryData['currency_symbol'],
                    "DecimalDigits" => 2,
                    "Rounding" => 2,
                    'CreatedOn' => $date,
                    'CreatedBy' => $actor->Id,
                    'ModifiedOn' => $date,
                    'ModifiedBy' => $actor->Id,
                ]);
            }

            // Insert country
            $countryId = DB::table('t_Countries')->insertGetId([
                "Name" => $countryData['name'],
                "CountryCode" => $countryData['iso2'],
                "PhoneCode" => $countryData['phonecode'],
                "Flag" => $countryData['emoji'],
                "CurrencyId" => $currencyId,
                'CreatedOn' => $date,
                'CreatedBy' => $actor->Id,
                'ModifiedOn' => $date,
                'ModifiedBy' => $actor->Id,
            ]);

            // Process states/regions
            if (!empty($countryData['states'])) {
                foreach ($countryData['states'] as $stateData) {
                    // Insert state/region
                    $stateId = DB::table('t_Localities')->insertGetId([
                        'Name' => $stateData['name'],
                        'LocationType' => $stateData['type'] ?? 'state',
                        'CreatedOn' => $date,
                        'LocalityID' => null,
                        'CountryId' => $countryId,
                        'CreatedBy' => $actor->Id,
                        'ModifiedOn' => $date,
                        'ModifiedBy' => $actor->Id,
                    ]);

                    // Process cities
                    if (!empty($stateData['cities'])) {
                        foreach ($stateData['cities'] as $cityData) {
                            DB::table('t_Localities')->insert([
                                'Name' => $cityData['name'],
                                'LocationType' => 'city',
                                'CountryId' => $countryId,
                                'LocalityID' => $stateId,
                                'CreatedOn' => $date,
                                'CreatedBy' => $actor->Id,
                                'ModifiedOn' => $date,
                                'ModifiedBy' => $actor->Id,
                            ]);
                        }
                    }
                }
            }
        }
    }

    /**
     * Get currency ID from currency code
     */
    private function getCurrencyId(string $currencyCode, string $currencySymbol): ?int
    {
        return DB::table('t_Currencies')
            ->where('Code', $currencyCode)
            ->orWhere('Symbol', $currencySymbol)
            ->value('Id');
    }

    /*  $user = SystemHelper::user();
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


      DB::table('t_Localities')->insert($cities->toArray());*/

}
