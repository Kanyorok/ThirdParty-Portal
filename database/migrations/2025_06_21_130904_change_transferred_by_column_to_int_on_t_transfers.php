<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_Transfers', function (Blueprint $table) {
            // Drop the old string column
            $table->dropColumn('TransferredBy');
        });

        Schema::table('t_Transfers', function (Blueprint $table) {
            // Add new foreignId column, nullable to avoid SQL Server errors
            $table->foreignId('TransferredBy')
                ->nullable()
                ->after('TransferDate')
                ->constrained('t_Users', 'Id');
        });
    }

    public function down(): void
    {
        Schema::table('t_Transfers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('TransferredBy');

            // Re-add the original string column (if rolling back)
            $table->string('TransferredBy')->nullable()->after('TransferDate');
        });
    }
};
