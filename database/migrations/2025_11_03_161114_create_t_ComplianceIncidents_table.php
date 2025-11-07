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
        Schema::create('t_ComplianceIncidents', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('ObligationID')->nullable();
            $table->string('Title');
            $table->text('Description')->nullable();
            $table->date('IncidentDate');
            $table->bigInteger('SeverityID');
            $table->bigInteger('ResponsibleUserID')->nullable();
            $table->string('Status', 50)->default('Open');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn')->useCurrent();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_compli__3214ec0729eb3874');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ComplianceIncidents');
    }
};
