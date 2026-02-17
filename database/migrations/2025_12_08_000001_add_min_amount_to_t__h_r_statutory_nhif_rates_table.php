<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_HRStatutoryNHIFRates', function (Blueprint $table) {
            if (!Schema::hasColumn('t_HRStatutoryNHIFRates', 'MinAmount')) {
                $table->decimal('MinAmount', 18, 2)->nullable()->after('EmployeeRate');
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_HRStatutoryNHIFRates', function (Blueprint $table) {
            if (Schema::hasColumn('t_HRStatutoryNHIFRates', 'MinAmount')) {
                $table->dropColumn('MinAmount');
            }
        });
    }
};
