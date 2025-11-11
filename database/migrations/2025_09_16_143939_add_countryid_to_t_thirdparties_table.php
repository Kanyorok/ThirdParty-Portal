<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('t_ThirdParties', function (Blueprint $table) {
            if (!Schema::hasColumn('t_ThirdParties', 'CountryId')) {
                $table->foreignId('CountryId')->nullable()->constrained('t_Countries', 'Id')->after('Country');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_ThirdParties', function (Blueprint $table) {
            if (Schema::hasColumn('t_ThirdParties', 'CountryId')) {
                $table->dropConstrainedForeignId('CountryId');
            }
        });
    }
};
