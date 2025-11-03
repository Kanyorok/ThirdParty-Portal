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
        Schema::create('t_MaintenanceRequest', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('RequestNumber')->unique();
            $table->bigInteger('Property');
            $table->bigInteger('Block')->nullable();
            $table->bigInteger('Floor')->nullable();
            $table->bigInteger('Unit')->nullable();
            $table->string('ReportedBy');
            $table->string('IssueDescription');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('IssueType');
            $table->bigInteger('Priority');

            $table->primary(['Id'], 'pk__t_mainte__3214ec0747532663');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_MaintenanceRequest');
    }
};
