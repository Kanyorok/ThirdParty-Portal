<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_HRKPIGoalItems', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->unsignedBigInteger('GoalID');
            $table->unsignedBigInteger('KpiItemID');
            $table->decimal('TargetValue', 18, 2)->nullable();
            $table->decimal('Weight', 8, 2)->default(0);
            $table->string('Notes', 255)->nullable();
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HRKPIGoalItems');
    }
};
