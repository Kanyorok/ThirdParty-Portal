<?php

namespace Database\Seeders;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SPSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fresh = DB::table('t_Reports')->count() === 0;

        $spPath = database_path('sps');

        $directories = collect(File::directories($spPath))->sort()->values()->all();
        foreach ($directories as $directory) {
            $baseName = basename($directory);
            if (str_starts_with($baseName, '0')) {
                continue;
            }
            if (! $fresh && str_starts_with($baseName, '2')) {
                continue;
            }

            $files = File::allFiles($directory);

            foreach ($files as $file) {
                if ($file->getExtension() === 'sql') {
                    try {
                        $sql = File::get($file->getPathname());
                    } catch (FileNotFoundException $e) {
                        continue;
                    }
                    DB::unprepared($sql);
                }
            }
        }
    }
}
