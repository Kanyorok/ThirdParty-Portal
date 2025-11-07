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
        Schema::create('t_DepartmentNeeds', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('NeedID')->unique();
            $table->bigInteger('BranchID');
            $table->bigInteger('DepartmentID');
            $table->bigInteger('ItemID');
            $table->integer('RequestedQty')->nullable();
            $table->decimal('EstimatedUnitCost');
            $table->text('Justification')->nullable();
            $table->string('Status')->nullable();
            $table->integer('FiscalYear');
            $table->string('PriorityLevel')->nullable();
            $table->boolean('IsEmergency')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->dateTime('RequestedDate')->nullable();
            $table->boolean('IsUsed')->default(false);

            $table->primary(['Id'], 'pk__t_depart__3214ec076d67149e');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_DepartmentNeeds');
    }
};
