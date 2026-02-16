<?php

namespace Database\Seeders;

use App\Helpers\SystemHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use JsonException;

class KenyaFirstSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting KenyaFirstSeeder...');

        $local = database_path('data/countries_states_cities.json');

        if (! file_exists($local)) {
            $this->command->error('Countries data file not found: ' . $local);

            return;
        }

        try {
            $this->command->info('Loading countries data...');
            $fileContent = file_get_contents($local);
            $countries = json_decode($fileContent, true, 512, JSON_THROW_ON_ERROR);

            if (! is_array($countries)) {
                throw new \RuntimeException('Parsed JSON is not an array.');
            }

            $this->command->info('Successfully loaded ' . count($countries) . ' countries.');

            $actor = SystemHelper::user();
            $date = now();

            // Find Kenya in the data
            $kenyaData = null;
            foreach ($countries as $countryData) {
                if (strtolower($countryData['name'] ?? '') === 'kenya') {
                    $kenyaData = $countryData;

                    break;
                }
            }

            if (! $kenyaData) {
                $this->command->error('Kenya not found in countries data!');

                return;
            }

            $this->command->info('Found Kenya data, processing...');

            // Process Kenya first
            $this->processCountry($kenyaData, $actor, $date, 0); // SortOrder = 0

            // Process other countries (first 49 to keep total at 50)
            $otherCountries = array_filter($countries, function ($country) {
                return strtolower($country['name'] ?? '') !== 'kenya';
            });

            $essentialCountries = array_slice($otherCountries, 0, 49);
            $sortOrder = 1;

            $this->command->info('Processing ' . count($essentialCountries) . ' other countries...');

            foreach ($essentialCountries as $countryData) {
                $this->processCountry($countryData, $actor, $date, $sortOrder);
                $sortOrder++;
            }

            $this->command->info('KenyaFirstSeeder completed successfully!');
        } catch (JsonException $e) {
            $this->command->error('Could not parse JSON data from ' . $local . '. Error: ' . $e->getMessage());
        } catch (\Throwable $e) {
            $this->command->error('Error during seeding: ' . $e->getMessage());
        }
    }

    /**
     * Process a single country and its localities
     */
    private function processCountry(array $countryData, $actor, $date, int $sortOrder): void
    {
        $countryName = $countryData['name'] ?? '(unknown)';
        $this->command->info("Processing: {$countryName} (SortOrder: {$sortOrder})");

        // Currency fields with fallbacks
        $currencyCode = $countryData['currency'] ?? null;
        $currencySymbol = $countryData['currency_symbol'] ?? '';
        $currencyName = $countryData['currency_name'] ?? '';

        $currencyId = $currencyCode ? $this->getCurrencyId($currencyCode, $currencySymbol) : null;

        if (! $currencyId && $currencyCode) {
            $currencyData = [
                'Name' => $currencyName ?: $currencyCode,
                'Code' => $currencyCode,
                'Symbol' => $currencySymbol,
                'SymbolNative' => $currencySymbol,
                'DecimalDigits' => 2,
                'Rounding' => 2,
                'CreatedOn' => $date,
                'CreatedBy' => $actor->Id,
                'ModifiedOn' => $date,
                'ModifiedBy' => $actor->Id,
            ];
            DB::table('t_Currencies')->updateOrInsert(
                ['Code' => $currencyCode],
                $currencyData
            );
            $currencyId = $this->getCurrencyId($currencyCode, $currencySymbol);
        }

        // Insert or update country with proper sort order
        $countryInsertData = [
            'Name' => $countryName,
            'CountryCode' => $countryData['iso2'] ?? '',
            'Iso3' => $countryData['iso3'] ?? '',
            'PhoneCode' => $countryData['phonecode'] ?? '',
            'Flag' => $countryData['emoji'] ?? '',
            'CurrencyId' => $currencyId,
            'IsActive' => 1,
            'SortOrder' => $sortOrder,
            'CreatedOn' => $date,
            'CreatedBy' => $actor->Id,
            'ModifiedOn' => $date,
            'ModifiedBy' => $actor->Id,
        ];

        // Skip Kenya if it already exists, otherwise process all countries
        if (strtolower($countryName) === 'kenya') {
            $existingKenya = DB::table('t_Countries')->where('Name', 'Kenya')->first();
            if ($existingKenya) {
                $this->command->info("Kenya already exists, skipping...");
                // Still need to process localities for Kenya
                $countryId = $existingKenya->Id;
                $this->processLocalities($countryData, $countryId, $countryName);

                return;
            }
        }

        DB::table('t_Countries')->updateOrInsert(
            ['Name' => $countryInsertData['Name']],
            $countryInsertData
        );

        $countryId = DB::table('t_Countries')
            ->where('Name', $countryInsertData['Name'])
            ->value('Id');

        // Process localities
        $this->processLocalities($countryData, $countryId, $countryName);
    }

    /**
     * Process localities (states and cities) for a country
     */
    private function processLocalities(array $countryData, $countryId, string $countryName): void
    {
        if (! $countryId || empty($countryData['states'])) {
            return;
        }

        $actor = SystemHelper::user();
        $date = now();

        $maxStates = strtolower($countryName) === 'kenya' ? 10 : 5; // More states for Kenya
        $majorStates = array_slice($countryData['states'], 0, $maxStates);
        $this->command->info("  Processing " . count($majorStates) . " states for {$countryName}");

        foreach ($majorStates as $stateData) {
            $stateName = $stateData['name'] ?? '(unknown state)';
            $stateType = $stateData['type'] ?? 'state';

            $stateInsertData = [
                'Name' => $stateName,
                'LocationType' => $stateType,
                'CreatedOn' => $date,
                'LocalityID' => null, // parent is the country
                'CountryId' => $countryId,
                'CreatedBy' => $actor->Id,
                'ModifiedOn' => $date,
                'ModifiedBy' => $actor->Id,
            ];

            DB::table('t_Localities')->updateOrInsert(
                [
                    'Name' => $stateInsertData['Name'],
                    'CountryId' => $countryId,
                    'LocationType' => $stateInsertData['LocationType'],
                ],
                $stateInsertData
            );

            $stateId = DB::table('t_Localities')
                ->where('Name', $stateInsertData['Name'])
                ->where('CountryId', $countryId)
                ->where('LocationType', $stateInsertData['LocationType'])
                ->value('Id');

            // Process cities
            if (! empty($stateData['cities']) && $stateId) {
                $maxCities = strtolower($countryName) === 'kenya' ? 20 : 10; // More cities for Kenya
                $majorCities = array_slice($stateData['cities'], 0, $maxCities);
                $this->command->info("    Processing " . count($majorCities) . " cities for {$stateName}");

                foreach ($majorCities as $cityData) {
                    $cityName = $cityData['name'] ?? '(unknown city)';
                    $cityInsertData = [
                        'Name' => $cityName,
                        'LocationType' => 'city',
                        'CountryId' => $countryId,
                        'LocalityID' => $stateId, // parent is the state
                        'CreatedOn' => $date,
                        'CreatedBy' => $actor->Id,
                        'ModifiedOn' => $date,
                        'ModifiedBy' => $actor->Id,
                    ];

                    DB::table('t_Localities')->updateOrInsert(
                        [
                            'Name' => $cityInsertData['Name'],
                            'CountryId' => $countryId,
                            'LocalityID' => $stateId,
                        ],
                        $cityInsertData
                    );
                }
            }
        }
    }

    /**
     * Get currency ID from currency code or symbol.
     */
    private function getCurrencyId(?string $currencyCode, ?string $currencySymbol): ?int
    {
        if (! $currencyCode && ! $currencySymbol) {
            return null;
        }

        $query = DB::table('t_Currencies');

        if ($currencyCode) {
            $query->where('Code', $currencyCode);
        }
        if ($currencySymbol) {
            $query->orWhere('Symbol', $currencySymbol);
        }

        return $query->value('Id');
    }
}
