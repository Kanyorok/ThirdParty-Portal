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
        Schema::create('t_PropertyInterest', static function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('PropertyId')->constrained('t_PropertyRegistry', 'Id');
            $table->foreignId('BlockId')->constrained('t_PropertyBlock', 'Id');
            $table->foreignId('FloorId')->constrained('t_PropertyFloor', 'Id');
            $table->foreignId('UnitId')->constrained('t_PropertyUnit', 'Id');
            $table->foreignId('TenantId')->constrained('t_TenantMaintenance', 'Id');
            $table->date('InterestedStartDate');
            $table->date('InterestedEndDate');
            $table->foreignId('PaymentFrequency')->constrained('t_CodeDetails', 'ID');
            $table->string('AdditionalInformation')->nullable();
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
        Schema::dropIfExists('t_PropertyInterest');
    }
};
