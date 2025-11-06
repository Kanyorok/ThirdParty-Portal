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
        Schema::create('t_TenantClearance', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->date('ExitDate');
            $table->boolean('FinalInspection');
            $table->boolean('AllDuesPaid');
            $table->boolean('KeysReturned');
            $table->bigInteger('DepositRefunded');
            $table->string('AdditionalNotes');
            $table->string('Status');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('LeaseId');

            $table->primary(['Id'], 'pk__t_tenant__3214ec07fda41fd2');
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
