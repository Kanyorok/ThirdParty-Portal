<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_HREmployees', function (Blueprint $table) {
            if (!Schema::hasColumn('t_HREmployees', 'Gender')) {
                $table->string('Gender', 20)->nullable()->after('Phone');
            }
            if (!Schema::hasColumn('t_HREmployees', 'DateOfBirth')) {
                $table->date('DateOfBirth')->nullable()->after('Gender');
            }
            if (!Schema::hasColumn('t_HREmployees', 'Address')) {
                $table->string('Address', 255)->nullable()->after('ContractType');
            }
            if (!Schema::hasColumn('t_HREmployees', 'PhotoPath')) {
                $table->string('PhotoPath', 500)->nullable()->after('Address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_HREmployees', function (Blueprint $table) {
            if (Schema::hasColumn('t_HREmployees', 'PhotoPath')) {
                $table->dropColumn('PhotoPath');
            }
            if (Schema::hasColumn('t_HREmployees', 'Address')) {
                $table->dropColumn('Address');
            }
            if (Schema::hasColumn('t_HREmployees', 'DateOfBirth')) {
                $table->dropColumn('DateOfBirth');
            }
            if (Schema::hasColumn('t_HREmployees', 'Gender')) {
                $table->dropColumn('Gender');
            }
        });
    }
};
