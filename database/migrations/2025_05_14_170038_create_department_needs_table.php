<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use phpDocumentor\Reflection\Types\Nullable;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_DepartmentNeeds', static function (Blueprint $table) {
            $table->id('Id');
            $table->string('NeedID')->unique();
            $table->foreignId('BranchID') ->constrained('t_Branches');//needBranch
            $table->foreignId('DepartmentID')->constrained('t_Departments');
            $table->foreignId('ItemID')->constrained('t_Items','Id');
            $table->integer('RequestedQty')->nullable();
            $table->decimal('EstimatedUnitCost')->Nullable();
            $table->text('Justification')->nullable();
            $table->string('Status')->nullable();
            $table->integer('FiscalYear')->required();
            $table->string('PriorityLevel')->nullable();
            $table->boolean('IsEmergency')->nullable();
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
        Schema::dropIfExists('t_DepartmentNeeds');
    }
};
