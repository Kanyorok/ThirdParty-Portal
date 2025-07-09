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
<<<<<<<< HEAD:database/migrations/2025_07_07_102035_create_r_f_q_sections_table.php
        Schema::create('t_RFQSection', function (Blueprint $table) {
            $table->id();
            $table->foreignId('SectionID')->constrained('t_RFQSettingSections', 'id');
            $table->foreignId('RFQID')->constrained('t_RFQ', 'Id'); // FK to t_RFQ
            $table->decimal('Weight', 5, 2)->default(0.00); // DECIMAL(5,2)
            $table->boolean('IsActive')->default(true); // BIT (boolean in Laravel)
            $table->text('Comments')->nullable(); // TEXT (optional)

========
        Schema::create('t_TenantClearance', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('Tenant')->constrained('t_TenantMaintenance', 'Id');
            $table->date('ExitDate');
            $table->boolean('FinalInspection');
            $table->boolean('AllDuesPaid');
            $table->boolean('KeysReturned');
            $table->foreignId('DepositRefunded')->constrained('t_CodeDetails', 'Id');
            $table->string('AdditionalNotes');
            $table->string('Status')->max(1);
>>>>>>>> dev:database/migrations/2025_05_20_173231_create_tenantclearance_table.php
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
        Schema::dropIfExists('t_RFQSection');
    }
};
