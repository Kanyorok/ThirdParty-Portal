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

        $source = 'https://raw.githubusercontent.com/techkey-martin/currencies/main/currencies.min.json'; // working JSON

        try {
            $currenciesJson = file_get_contents($source);

            if (!is_string($currenciesJson)) {
                throw new ErroredException('Unable to fetch the currencies data.');
            }

            $currencies = json_decode($currenciesJson, true, 512, JSON_THROW_ON_ERROR);

            if (!$currencies) {
                throw new ErroredException('Unable to decode currencies data.');
            }
        } catch (Throwable $e) {
            Log::error('Currency fetch error: ' . $e->getMessage());
            SystemHelper::notifyAdmin('Could not fetch currencies data from ' . $source);
            return;
        }

        $insertData = [];
        foreach ($currencies as $code => $currency) {
            $insertData[] = [
                "Name" => $currency['name'] ?? '',
                "Code" => $code,
                "Symbol" => $currency['symbol'] ?? '',
                "SymbolNative" => $currency['symbolNative'] ?? '',
                "DecimalDigits" => $currency['decimals'] ?? 0,
                "Rounding" => 0,
                "Demonym" => $currency['demonym'] ?? null,
                "MajorSingle" => $currency['majorSingle'] ?? null,
                "MajorPlural" => $currency['majorPlural'] ?? null,
                "ISOnum" => $currency['ISOnum'] ?? null,
                "MinorSingle" => $currency['minorSingle'] ?? null,
                "MinorPlural" => $currency['minorPlural'] ?? null,
                "ISOdigits" => $currency['ISOdigits'] ?? null,
                "Decimals" => $currency['decimals'] ?? null,
                "NumToBasic" => $currency['numToBasic'] ?? null,
                "CreatedOn" => $date,
                "CreatedBy" => $user->Id,
                "ModifiedOn" => $date,
                "ModifiedBy" => $user->Id,
            ];
        }

        DB::table('t_Currencies')->upsert($insertData, ['Code'], array_keys($insertData[0]));
    }
}
