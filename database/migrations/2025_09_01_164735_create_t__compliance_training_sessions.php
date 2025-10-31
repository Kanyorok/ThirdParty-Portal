<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_ComplianceTrainingSessions', function (Blueprint $table) {
            $table->id('Id');
            $table->string('Topic', 255);
            $table->foreignId('TrainingTypeID')->nullable()->constrained('t_TrainingTypes', 'Id');
            $table->string('Facilitator', 150)->nullable();
            $table->date('SessionDate');
            $table->string('Duration', 50)->nullable();
            $table->string('MaterialsFileName', 255)->nullable();
            $table->string('MaterialsMimeType', 100)->nullable();
            $table->string('MaterialsFilePath', 500)->nullable();

            $table->unsignedBigInteger('CreatedBy');
            $table->timestamp('CreatedOn')->useCurrent();
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
