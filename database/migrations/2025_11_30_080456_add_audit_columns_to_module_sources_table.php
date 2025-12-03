<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('t_ModuleSources', function (Blueprint $table) {
            $table->foreignId('CreatedBy')->nullable()->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn')->nullable();
            $table->foreignId('ModifiedBy')->nullable()->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_ModuleSources', function (Blueprint $table) {
            $table->dropForeign(['CreatedBy']);
            $table->dropColumn('CreatedBy');
            $table->dropColumn('CreatedOn');
            $table->dropForeign(['ModifiedBy']);
            $table->dropColumn('ModifiedBy');
            $table->dropColumn('ModifiedOn');
        });
    }
};
