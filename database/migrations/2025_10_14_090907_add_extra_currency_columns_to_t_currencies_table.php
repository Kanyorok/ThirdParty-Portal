<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_Currencies', function (Blueprint $table) {
            $table->string('Demonym')->nullable()->after('Name');
            $table->string('MajorSingle')->nullable()->after('Demonym');
            $table->string('MajorPlural')->nullable()->after('MajorSingle');
            $table->integer('ISOnum')->nullable()->after('MajorPlural');
            $table->string('MinorSingle')->nullable()->after('ISOnum');
            $table->string('MinorPlural')->nullable()->after('MinorSingle');
            $table->integer('ISOdigits')->nullable()->after('MinorPlural');
            $table->integer('Decimals')->nullable()->after('ISOdigits');
            $table->integer('NumToBasic')->nullable()->after('Decimals');
        });
    }

    public function down(): void
    {
        Schema::table('t_Currencies', function (Blueprint $table) {
            $table->dropColumn([
                'Demonym',
                'MajorSingle',
                'MajorPlural',
                'ISOnum',
                'MinorSingle',
                'MinorPlural',
                'ISOdigits',
                'Decimals',
                'NumToBasic',
            ]);
        });
    }
};
