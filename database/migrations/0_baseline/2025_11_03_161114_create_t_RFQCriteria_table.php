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
        Schema::create('t_RFQCriteria', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('SectionID');
            $table->bigInteger('RFQID');
            $table->bigInteger('CriteriaID');
            $table->decimal('MaxScore', 5)->default(0);
            $table->boolean('IsActive')->default(true);
            $table->text('Comments')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['id'], 'pk__t_rfqcri__3213e83fb04c8ff2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_RFQCriteria');
    }
};
