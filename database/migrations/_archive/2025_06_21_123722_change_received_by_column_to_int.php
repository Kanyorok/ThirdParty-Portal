<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_TransactionReceipts', function (Blueprint $table) {
            // Drop the existing string column
            $table->dropColumn('ReceivedBy');
        });

        Schema::table('t_TransactionReceipts', function (Blueprint $table) {
            $table->foreignId('ReceivedBy')
                ->nullable() // Allow nulls first
                ->after('TransferId')
                ->constrained('t_Users', 'Id');
        });

    }

    public function down(): void
    {
        Schema::table('t_TransactionReceipts', function (Blueprint $table) {
            // Drop the foreign key column
            $table->dropForeign(['ReceivedBy']);
            $table->dropColumn('ReceivedBy');

            // Re-add the original string column
            $table->string('ReceivedBy')->nullable();
        });
    }
};
