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
        Schema::create('t_Surveys', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('SurveyID', 100)->unique();
            $table->string('Label', 250);
            $table->text('Notes')->nullable();
            $table->dateTime('StartOn');
            $table->dateTime('EndOn');
            $table->char('Status', 2);
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_survey__3214ec07d3e2b88c');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Surveys');
    }
};
