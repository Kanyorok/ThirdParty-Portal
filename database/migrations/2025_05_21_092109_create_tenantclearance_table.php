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
        Schema::create('t_TenantClearance', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('Tenant')->constrained('t_TenantMaintenance', 'Id');
            $table->date('ExitDate');
            $table->boolean('FinalInspection');
            $table->boolean('AllDuesPaid');
            $table->boolean('KeysReturned');
            $table->foreignId('DepositRefunded')->constrained('t_CodeDetails', 'Id');
            $table->string('AdditionalNotes');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_TenantClearance');
    }
};
