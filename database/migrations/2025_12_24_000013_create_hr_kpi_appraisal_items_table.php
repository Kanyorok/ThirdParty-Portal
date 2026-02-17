<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_HRKPIAppraisalItems', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->unsignedBigInteger('AppraisalID');
            $table->unsignedBigInteger('GoalItemID');
            $table->decimal('ActualValue', 18, 2)->nullable();
            $table->decimal('Score', 8, 2)->default(0);
            $table->unsignedBigInteger('RatingScaleID')->nullable();
            $table->string('Comments', 255)->nullable();
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HRKPIAppraisalItems');
    }
};
