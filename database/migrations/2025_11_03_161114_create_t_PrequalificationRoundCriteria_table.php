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
        Schema::create('t_PrequalificationRoundCriteria', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('RoundId');
            $table->bigInteger('SectionId');
            $table->bigInteger('CriteriaId');
            $table->boolean('Included');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->integer('Weight')->default(0);

            $table->primary(['Id'], 'pk__t_prequa__3214ec07950f154b');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_PrequalificationRoundCriteria');
    }
};
