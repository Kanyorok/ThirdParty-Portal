<?php

namespace Database\Seeders;

use App\Exceptions\ErroredException;
use App\Helpers\SystemHelper;
use DB;
use Illuminate\Database\Seeder;
use Log;
use Throwable;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = SystemHelper::user();
        $date = now();

        $source = 'https://raw.githubusercontent.com/leequixxx/currencies.json/master/currencies.json';#"https://github.com/leequixxx/currencies.json/blob/master/currencies.json";

        $currenciesJson = file_get_contents($source);
        try {
            if (!is_string($currenciesJson)) {
                throw new ErroredException('Unable to fetch the currencies data.');
            }
            $currencies = json_decode($currenciesJson, true, 512, JSON_THROW_ON_ERROR);
            if (!$currencies) {
                throw new ErroredException('Unable to fetch or decode the currencies data.');
            }
        } catch (Throwable $e) {
            Log::error('Could not fetch currencies data from ' . $source . '. Error: ' . $e->getMessage());
            SystemHelper::notifyAdmin('Could not fetch currencies data from ' . $source . '.');
            return;
        }


        $insertData = [];
        foreach ($currencies as $currency) {
            $insertData[] = [
                "Name" => $currency['name'],
                "Code" => $currency['code'],
                "Symbol" => $currency['symbol'],
                "SymbolNative" => $currency['symbolNative'],
                "DecimalDigits" => $currency['decimalDigits'],
                "Rounding" => (float) $currency['rounding'],
                'CreatedOn'    => $date,
                'CreatedBy'    => $user->Id,
                'ModifiedOn'   => $date,
                'ModifiedBy'   => $user->Id,
            ];
        }

        DB::table('t_Currencies')->insert($insertData);
    }
}
