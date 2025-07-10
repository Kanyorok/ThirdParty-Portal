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
        Schema::create('t_PrequalificationRoundCriteria', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('RoundId')->constrained('t_PrequalificationPeriod', 'Id');
            $table->foreignId('SectionId')->constrained('t_Sections', 'id');
            $table->foreignId('CriteriaId')->constrained('t_Criterias', 'id');
            $table->boolean('Included');
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
        Schema::dropIfExists('t_PrequalificationRoundCriteria');
    }
};
