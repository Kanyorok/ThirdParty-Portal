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
        Schema::create('t_ComplianceTrainingSessions', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('Topic');
            $table->bigInteger('TrainingTypeID')->nullable();
            $table->string('Facilitator', 150)->nullable();
            $table->date('SessionDate');
            $table->string('Duration', 50)->nullable();
            $table->string('MaterialsFileName')->nullable();
            $table->string('MaterialsMimeType', 100)->nullable();
            $table->string('MaterialsFilePath', 500)->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn')->useCurrent();

            $table->primary(['Id'], 'pk__t_compli__3214ec076d5d19a4');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ComplianceTrainingSessions');
    }
};
