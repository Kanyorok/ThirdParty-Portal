<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Enums\Procurement\PrequalificationApplicationEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('t_SupplierPrequalificationApplications', function (Blueprint $table) {
            // care about data type issues??
            $table->dropColumn('Status');
        });

        Schema::table('t_SupplierPrequalificationApplications', function (Blueprint $table) {
            $enumValues = array_column(PrequalificationApplicationEnum::cases(), 'value');
            $table->enum('Status', $enumValues)->default(PrequalificationApplicationEnum::Submitted->value);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_SupplierPrequalificationApplications', function (Blueprint $table) {
            $table->dropColumn('Status');
        });

        Schema::table('t_SupplierPrequalificationApplications', function (Blueprint $table) {
            $table->string('Status', 50)->default('Submitted');
        });
    }
};
