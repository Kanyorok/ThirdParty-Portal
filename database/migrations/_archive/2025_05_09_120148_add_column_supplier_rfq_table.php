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
        Schema::table('t_RFQ_Supplier', function (Blueprint $table) {
            $table->dateTime('CreatedOn')->nullable()->after('Status')->comment('Timestamp when the record was created');
            $table->dateTime('ModifiedOn')->nullable()->after('CreatedOn')->comment('Timestamp when the record was last modified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_RFQ_Supplier', function (Blueprint $table) {
            $table->dropColumn(['CreatedOn', 'ModifiedOn']);
        });
    }
};
