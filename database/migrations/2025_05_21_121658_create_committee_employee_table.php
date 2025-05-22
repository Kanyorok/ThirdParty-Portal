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
        Schema::create('t_Committee_Employee', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('EmployeeId');
            $table->unsignedBigInteger('CommitteeId');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        
            $table->foreign('EmployeeId')->references('Id')->on('t_Employees')->onDelete('cascade');
            $table->foreign('CommitteeId')->references('Id')->on('t_Committees')->onDelete('cascade');
            $table->unique(['EmployeeId', 'CommitteeId']); // prevent duplicates
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Committee_Employee');
    }
};
