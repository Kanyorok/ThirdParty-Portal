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
        $this->command->info('Starting optimized LocalitySeeder...');
        
        $local = database_path('data/countries_states_cities.json');

        // Fetch and decode JSON data
        if (!file_exists($local)) {
            $this->command->error('Countries data file not found: ' . $local);
            return;
        }

        try {
            $countries = json_decode(file_get_contents($local), true, 512, JSON_THROW_ON_ERROR);
            $this->command->info('Loaded ' . count($countries) . ' countries');
        } catch (JsonException $e) {
            $this->command->error('Could not fetch countries-states-cities-database data from ' . $local . '. Error: ' . $e->getMessage());
            return;
        }
        
        $actor = SystemHelper::user();
        $date = now();

        // Note: SQL Server doesn't support disabling foreign key checks like MySQL
        // We'll rely on updateOrInsert to handle duplicates gracefully

        try {
            // Process only essential countries (first 50) to avoid timeout
            $essentialCountries = array_slice($countries, 0, 50);
            $this->command->info('Processing essential countries (first 50)...');

            foreach ($essentialCountries as $countryData) {
                $this->command->info('Processing country: ' . $countryData['name']);

                $currencyId = $this->getCurrencyId($countryData['currency'], $countryData['currency_symbol']);
                if (!$currencyId) {
                    $currencyData = [
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
                    ];
                    
                    DB::table('t_Currencies')->updateOrInsert(
                        ['Code' => $countryData['currency']],
                        $currencyData
                    );
                    $currencyId = $this->getCurrencyId($countryData['currency'], $countryData['currency_symbol']);
                }

                // Insert or update country
                $countryInsertData = [
                    "Name" => $countryData['name'],
                    "CountryCode" => $countryData['iso2'],
                    "PhoneCode" => $countryData['phonecode'],
                    "Flag" => $countryData['emoji'],
                    "CurrencyId" => $currencyId,
                    'CreatedOn' => $date,
                    'CreatedBy' => $actor->Id,
                    'ModifiedOn' => $date,
                    'ModifiedBy' => $actor->Id,
                ];
                
                DB::table('t_Countries')->updateOrInsert(
                    ['Name' => $countryInsertData['Name']],
                    $countryInsertData
                );
                
                $countryId = DB::table('t_Countries')
                    ->where('Name', $countryInsertData['Name'])
                    ->value('Id');

                // Process only major states/regions (limit to first 5 per country)
                if (!empty($countryData['states'])) {
                    $majorStates = array_slice($countryData['states'], 0, 5);
                    foreach ($majorStates as $stateData) {
                        // Insert or update state/region
                        $stateInsertData = [
                            'Name' => $stateData['name'],
                            'LocationType' => $stateData['type'] ?? 'state',
                            'CreatedOn' => $date,
                            'LocalityID' => null,
                            'CountryId' => $countryId,
                            'CreatedBy' => $actor->Id,
                            'ModifiedOn' => $date,
                            'ModifiedBy' => $actor->Id,
                        ];
                        
                        DB::table('t_Localities')->updateOrInsert(
                            ['Name' => $stateInsertData['Name'], 'CountryId' => $countryId, 'LocationType' => $stateInsertData['LocationType']],
                            $stateInsertData
                        );
                        
                        $stateId = DB::table('t_Localities')
                            ->where('Name', $stateInsertData['Name'])
                            ->where('CountryId', $countryId)
                            ->where('LocationType', $stateInsertData['LocationType'])
                            ->value('Id');

                        // Process only major cities (limit to first 10 per state)
                        if (!empty($stateData['cities']) && $stateId) {
                            $majorCities = array_slice($stateData['cities'], 0, 10);
                            foreach ($majorCities as $cityData) {
                                $cityInsertData = [
                                    'Name' => $cityData['name'],
                                    'LocationType' => 'city',
                                    'CountryId' => $countryId,
                                    'LocalityID' => $stateId,
                                    'CreatedOn' => $date,
                                    'CreatedBy' => $actor->Id,
                                    'ModifiedOn' => $date,
                                    'ModifiedBy' => $actor->Id,
                                ];
                                
                                DB::table('t_Localities')->updateOrInsert(
                                    ['Name' => $cityInsertData['Name'], 'CountryId' => $countryId, 'LocalityID' => $stateId],
                                    $cityInsertData
                                );
                            }
                        }
                    }
                }
            }
            
            $this->command->info('LocalitySeeder completed successfully with essential data!');
        } catch (\Exception $e) {
            $this->command->error('Error during seeding: ' . $e->getMessage());
            throw $e;
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
