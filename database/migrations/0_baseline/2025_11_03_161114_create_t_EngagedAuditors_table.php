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
        Schema::create('t_EngagedAuditors', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('AuditorId');
            $table->date('EngagementStartDate');
            $table->date('EngagementEndDate')->nullable();
            $table->string('EngagementStatus')->default('Engaged');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_engage__3214ec07826b641a');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_EngagedAuditors');
    }
};
