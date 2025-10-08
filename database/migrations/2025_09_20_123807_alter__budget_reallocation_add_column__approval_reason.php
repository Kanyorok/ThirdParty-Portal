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
        Schema::table('t_BudgetReallocations', function (Blueprint $table) {
            $table->text('ApprovalReason')->nullable();

            $table->foreignId('ModifiedBy')
                  ->nullable()
                  ->constrained('t_Users', 'Id');

            $table->dateTime('ModifiedOn')->nullable();

            $table->foreignId('DeletedBy')
                  ->nullable()
                  ->constrained('t_Users', 'Id');

            $table->softDeletes('DeletedOn')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_BudgetReallocations', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['ModifiedBy']);
            $table->dropForeign(['DeletedBy']);

            // Then drop the columns
            $table->dropColumn([
                'ApprovalReason',
                'ModifiedBy',
                'ModifiedOn',
                'DeletedBy',
                'DeletedOn',
            ]);
        });
    }
};
