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
        Schema::create('t_PrequalificationEvaluations', function (Blueprint $table) {
            $table->id('EvaluationID');

            $table->foreignId('ApplicationID')->constrained('t_SupplierPrequalificationApplications', 'ApplicationID')->onDelete('cascade');
            $table->foreignId('EvaluatorID')->constrained('t_Users', 'Id')->onDelete('cascade');

            $table->foreignId('SectionID')->constrained('t_Sections', 'id')->onDelete('cascade');
            $table->foreignId('id')->constrained('t_Criterias', 'id')->onDelete('no action');

            $table->float('Score')->nullable();
            $table->float('MaxScore')->nullable();
            $table->text('Remarks')->nullable();

            $table->timestamp('EvaluatedOn')->nullable();
            $table->timestamp('CreatedOn')->nullable();
            $table->timestamp('ModifiedOn')->nullable();
            $table->timestamp('DeletedOn')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_PrequalificationEvaluations');
    }
};
