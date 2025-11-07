<?php
namespace Database\Seeders;

use App\Exceptions\ErroredException;
use App\Helpers\SystemHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Throwable;


class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $user = SystemHelper::user();
        $date = now();
        $source = 'https://raw.githubusercontent.com/ourworldincode/currency/main/currencies.json';
        try {
            $json = file_get_contents($source);
            if (!is_string($json)) {
                throw new ErroredException('Unable to fetch the currencies data.');
            }

            $currencies = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($currencies)) {
                throw new ErroredException('Unable to decode currencies data.');
            }
        } catch (Throwable $e) {
            Log::error('Could not fetch/parse currencies data from ' . $source . '. Error: ' . $e->getMessage());
            // notify admin if you have such helper
            if (method_exists(SystemHelper::class, 'notifyAdmin')) {
                SystemHelper::notifyAdmin('Could not fetch currencies data from ' . $source . '.');
            }
            return;
        }

        $now = $date->toDateTimeString();
        $userId = $user->Id ?? null;

        $upserts = [];

        foreach ($currencies as $code => $c) {
            // Map fields defensively
            $name = $c['name'] ?? ($c['majorSingle'] ?? $code);
            $symbol = $c['symbol'] ?? null;
            $symbolNative = $c['symbolNative'] ?? ($c['symbol'] ?? null);

            // DecimalDigits: prefer ISOdigits, fallback to decimals, else 0
            $decimalDigits = isset($c['ISOdigits']) ? (int) $c['ISOdigits'] : (isset($c['decimals']) ? (int) $c['decimals'] : 0);

            $isOnum = null;
            if (isset($c['ISOnum'])) {
                // Some sources use string or number; cast to int safely
                $isOnum = is_numeric($c['ISOnum']) ? (int) $c['ISOnum'] : null;
            }

            $upserts[] = [
                'Name'          => $name,
                'Demonym'       => $c['demonym'] ?? null,
                'Code'          => $code,
                'Symbol'        => $symbol,
                'SymbolNative'  => $symbolNative,
                'DecimalDigits' => $decimalDigits,
                'Rounding'      => isset($c['rounding']) ? (float)$c['rounding'] : 0.0,
                'MajorSingle'   => $c['majorSingle'] ?? null,
                'MajorPlural'   => $c['majorPlural'] ?? null,
                'ISOnum'        => $isOnum,
                'MinorSingle'   => $c['minorSingle'] ?? null,
                'MinorPlural'   => $c['minorPlural'] ?? null,
                'ISOdigits'     => isset($c['ISOdigits']) ? (int)$c['ISOdigits'] : null,
                'Decimals'      => isset($c['decimals']) ? (int)$c['decimals'] : null,
                'NumToBasic'    => isset($c['numToBasic']) ? (int)$c['numToBasic'] : null,
                'CreatedOn'     => $now,
                'CreatedBy'     => $userId,
                'ModifiedOn'    => $now,
                'ModifiedBy'    => $userId,
            ];
        }

        if (empty($upserts)) {
            Log::warning('CurrencySeeder: no currencies to upsert.');
            return;
        }

        // SQL Server supports a maximum of 2100 parameters per statement.
        // Compute a safe chunk size based on number of columns in each row.
        $columnsPerRow = count($upserts[0]);
        $maxParams = 2000; // keep headroom under 2100
        $chunkSize = max(1, intdiv($maxParams, max(1, $columnsPerRow)));
        // Optional: disable query log for memory/perf when doing many upserts
        DB::connection()->disableQueryLog();
        $updateColumns = [
            'Name',
            'Demonym',
            'Symbol',
            'SymbolNative',
            'DecimalDigits',
            'Rounding',
            'MajorSingle',
            'MajorPlural',
            'ISOnum',
            'MinorSingle',
            'MinorPlural',
            'ISOdigits',
            'Decimals',
            'NumToBasic',
            'ModifiedOn',
            'ModifiedBy'
        ];

        foreach (array_chunk($upserts, $chunkSize) as $chunk) {
            DB::table('t_Currencies')->upsert($chunk, ['Code'], $updateColumns);
        }

        Log::info('CurrencySeeder: upserted ' . count($upserts) . ' currencies.');
    }
}
