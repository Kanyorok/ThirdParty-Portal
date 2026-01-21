<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_BancassuranceReferrals', function (Blueprint $table) {
            // Drop columns
            $table->dropColumn([
                'ClientName',
                'ClientIDNumber',
                'ClientPhone',
                'ClientEmail',
            ]);

            // Add new column
            $table->foreignId('ClientId')->nullable()->constrained('t_BancassuranceCustomers','Id')->after('Id');
        });
    }

    public function down(): void
    {
        Schema::table('t_BancassuranceReferrals', function (Blueprint $table) {
            // Rollback: add back removed columns
            $table->string('ClientName');
            $table->string('ClientIDNumber');
            $table->string('ClientPhone');
            $table->string('ClientEmail');

            // Rollback: remove ClientId column
            $table->dropForeign(['ClientId']);
            $table->dropColumn('ClientId');
        });
    }
};
