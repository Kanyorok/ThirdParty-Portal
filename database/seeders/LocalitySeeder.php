<?php

namespace Database\Seeders;

use App\Helpers\SystemHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use JsonException;

class LocalitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting optimized LocalitySeeder...');

        // Capture original memory_limit and try to raise it defensively
        $originalMemoryLimit = ini_get('memory_limit') ?: '128M';
        $raised = @ini_set('memory_limit', '2G');
        if ($raised === false) {
            $this->command->warn('Could not raise memory_limit to 2G; continuing with current limit (' . $originalMemoryLimit . ').');
        } else {
            $this->command->info('Increased memory limit to 2G (was: ' . $originalMemoryLimit . ').');
        }

        // Reduce memory overhead from Laravel connection query log
        if (method_exists(DB::connection(), 'disableQueryLog')) {
            DB::connection()->disableQueryLog();
        }

        $local = database_path('data/countries_states_cities.json');

        if (!file_exists($local)) {
            $this->command->error('Countries data file not found: ' . $local);
            // finally{} will still run and attempt safe restore
            return;
        }

        $countries = null;

        $countries = null;

        try {
            $this->command->info('Loading countries data (large file)…');
            $fileContent = file_get_contents($local);

            if ($fileContent === false) {
                throw new \RuntimeException('file_get_contents failed for ' . $local);
            }

            $this->command->info('File loaded, parsing JSON…');
            $countries = json_decode($fileContent, true, 512, JSON_THROW_ON_ERROR);

            // Free the raw file content ASAP
            unset($fileContent);

            if (!is_array($countries)) {
                throw new \RuntimeException('Parsed JSON is not an array.');
            }

            $this->command->info('Successfully loaded ' . count($countries) . ' countries.');

            $actor = SystemHelper::user();
            $date  = now();

            // Filter to Africa and Asia only
            $allowedRegions = ['Africa', 'Asia'];
            $filtered = [];
            foreach ($countries as $c) {
                $region = $c['region'] ?? null;
                if ($region && in_array($region, $allowedRegions, true)) {
                    $filtered[] = $c;
                }
            }

            // Reorder so that Kenya comes first within the filtered list
            $kenyaIndex = null;
            foreach ($filtered as $idx => $c) {
                if (isset($c['name']) && strtolower($c['name']) === 'kenya') {
                    $kenyaIndex = $idx;
                    break;
                }
            }

            if ($kenyaIndex !== null) {
                $kenyaData = $filtered[$kenyaIndex];
                unset($filtered[$kenyaIndex]);
                $filtered = array_values($filtered);
                array_unshift($filtered, $kenyaData);
            }

            // Optionally sort remaining countries alphabetically by name after Kenya
            if (count($filtered) > 1) {
                $first = array_shift($filtered); // Kenya if present
                usort($filtered, function ($a, $b) {
                    return strcasecmp($a['name'] ?? '', $b['name'] ?? '');
                });
                array_unshift($filtered, $first);
            }

            $totalCountries = count($filtered);
            $this->command->info("Seeding {$totalCountries} Africa and Asia countries (Kenya first)…");

            foreach ($filtered as $index => $countryData) {
                $progress = $index + 1;
                $countryName = $countryData['name'] ?? '(unknown)';
                if ($index === 0 || $progress % 10 === 0 || $progress === $totalCountries) {
                    $this->command->info("Processing country {$progress}/{$totalCountries}: {$countryName}");
                }

                // Currency fields with fallbacks
                $currencyCode   = $countryData['currency']         ?? null;
                $currencySymbol = $countryData['currency_symbol']  ?? '';
                $currencyName   = $countryData['currency_name']    ?? '';

                $currencyId = $currencyCode ? $this->getCurrencyId($currencyCode, $currencySymbol) : null;

                if (!$currencyId && $currencyCode) {
                    $currencyData = [
                        'Name'          => $currencyName ?: $currencyCode,
                        'Code'          => $currencyCode,
                        'Symbol'        => $currencySymbol,
                        'SymbolNative'  => $currencySymbol,
                        'DecimalDigits' => 2,
                        'Rounding'      => 2,
                        'CreatedOn'     => $date,
                        'CreatedBy'     => $actor->Id,
                        'ModifiedOn'    => $date,
                        'ModifiedBy'    => $actor->Id,
                    ];
                    DB::table('t_Currencies')->updateOrInsert(
                        ['Code' => $currencyCode],
                        $currencyData
                    );
                    $currencyId = $this->getCurrencyId($currencyCode, $currencySymbol);
                }

                // Insert or update country
                $countryInsertData = [
                    'Name'        => $countryName,
                    'CountryCode' => $countryData['iso2']     ?? '',
                    'Iso3'        => $countryData['iso3']     ?? null,
                    'PhoneCode'   => $countryData['phonecode'] ?? '',
                    'Flag'        => $countryData['emoji']     ?? '',
                    'CurrencyId'  => $currencyId,
                    'IsActive'    => 1,
                    'SortOrder'   => $index, // Kenya will be 0, others sequential within Africa+Asia
                    'CreatedOn'   => $date,
                    'CreatedBy'   => $actor->Id,
                    'ModifiedOn'  => $date,
                    'ModifiedBy'  => $actor->Id,
                ];

                DB::table('t_Countries')->updateOrInsert(
                    ['Name' => $countryInsertData['Name']],
                    $countryInsertData
                );

                $countryId = DB::table('t_Countries')
                    ->where('Name', $countryInsertData['Name'])
                    ->value('Id');

                // Process all states/regions
                if (!empty($countryData['states']) && $countryId) {
                    $states = $countryData['states'];
                    // Intentionally minimize console output for performance

                    foreach ($states as $stateData) {
                        $stateName = $stateData['name'] ?? '(unknown state)';
                        $stateType = $stateData['type'] ?? 'state';

                        $stateInsertData = [
                            'Name'         => $stateName,
                            'LocationType' => $stateType,
                            'CreatedOn'    => $date,
                            'LocalityID'   => null,          // parent is the country
                            'CountryId'    => $countryId,
                            'CreatedBy'    => $actor->Id,
                            'ModifiedOn'   => $date,
                            'ModifiedBy'   => $actor->Id,
                        ];

                        DB::table('t_Localities')->updateOrInsert(
                            [
                                'Name'         => $stateInsertData['Name'],
                                'CountryId'    => $countryId,
                                'LocationType' => $stateInsertData['LocationType'],
                            ],
                            $stateInsertData
                        );

                        $stateId = DB::table('t_Localities')
                            ->where('Name', $stateInsertData['Name'])
                            ->where('CountryId', $countryId)
                            ->where('LocationType', $stateInsertData['LocationType'])
                            ->value('Id');

                        // Process all cities for the state
                        if (!empty($stateData['cities']) && $stateId) {
                            $cities = $stateData['cities'];
                            // Intentionally minimize console output for performance

                            foreach ($cities as $cityData) {
                                $cityName = $cityData['name'] ?? '(unknown city)';
                                $cityInsertData = [
                                    'Name'         => $cityName,
                                    'LocationType' => 'city',
                                    'CountryId'    => $countryId,
                                    'LocalityID'   => $stateId,   // parent is the state
                                    'CreatedOn'    => $date,
                                    'CreatedBy'    => $actor->Id,
                                    'ModifiedOn'   => $date,
                                    'ModifiedBy'   => $actor->Id,
                                ];

                                DB::table('t_Localities')->updateOrInsert(
                                    [
                                        'Name'       => $cityInsertData['Name'],
                                        'CountryId'  => $countryId,
                                        'LocalityID' => $stateId,
                                    ],
                                    $cityInsertData
                                );
                            }

                            // Free per-iteration arrays ASAP
                            unset($majorCities);
                        }
                    }

                    // Free per-country arrays ASAP
                    unset($majorStates);
                }
            }

            // Free up countries data from memory
            unset($countries, $filtered);

            $this->command->info('LocalitySeeder completed successfully with Africa + Asia data!');

        } catch (JsonException $e) {
            $this->command->error('Could not parse JSON data from ' . $local . '. Error: ' . $e->getMessage());
        } catch (\Throwable $e) {
            $this->command->error('Error during seeding: ' . $e->getMessage());
            Log::error('LocalitySeeder failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        } finally {
            // Ensure large references are dropped before attempting to lower the memory limit
            if (isset($countries)) unset($countries);
            if (function_exists('gc_collect_cycles')) gc_collect_cycles();

            $this->safeRestoreMemoryLimit($originalMemoryLimit);
        }
    }

    /**
     * Get currency ID from currency code or symbol.
     */
    private function getCurrencyId(?string $currencyCode, ?string $currencySymbol): ?int
    {
        if (!$currencyCode && !$currencySymbol) {
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

    /**
     * Safely restore memory_limit to the original value.
     * Skips restoration if current usage exceeds the target limit, or if original was -1.
     */
    private function safeRestoreMemoryLimit(string $originalLimit): void
    {
        $originalLimit = trim($originalLimit);

        // If original was unlimited, nothing to do
        if ($originalLimit === '-1') {
            $this->command->info('Original memory limit was unlimited (-1); leaving current setting.');
            return;
        }

        $currentUsage     = memory_get_usage(true);
        $targetLimitBytes = $this->parseMemoryLimit($originalLimit);

        if ($currentUsage > $targetLimitBytes) {
            $this->command->warn(
                'Skipping memory_limit restore to ' . $originalLimit .
                ' because current usage (' . $this->formatBytes($currentUsage) . ') exceeds it. Leaving limit unchanged.'
            );
            return;
        }

        $result = @ini_set('memory_limit', $originalLimit);
        if ($result === false) {
            $this->command->warn('ini_set refused to restore memory_limit to ' . $originalLimit . '. Leaving unchanged.');
        } else {
            $this->command->info('Restored memory limit to: ' . $originalLimit);
        }
    }

    /**
     * Parse a memory_limit string (e.g., "128M", "2G", "-1") into bytes.
     */
    private function parseMemoryLimit(string $limit): int
    {
        $limit = trim($limit);

        if ($limit === '-1') {
            return PHP_INT_MAX; // treat unlimited as "infinite" for comparisons
        }

        if (is_numeric($limit)) {
            return (int) $limit;
        }

        $last  = strtolower(substr($limit, -1));
        $value = (float) substr($limit, 0, -1);

        switch ($last) {
            case 'g': $value *= 1024;
            // no break
            case 'm': $value *= 1024;
            // no break
            case 'k': $value *= 1024;
                break;
            default:
                $value = (float) $limit; // assume bytes if unit missing
        }

        return (int) $value;
    }

    /**
     * Format bytes to human-readable string.
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
