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
        Schema::create('t_ComplianceIncidentActions', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('IncidentID');
            $table->text('RootCause')->nullable();
            $table->text('CorrectiveAction')->nullable();
            $table->bigInteger('ActionOwnerID')->nullable();
            $table->date('DueDate')->nullable();
            $table->string('Status', 50)->default('Pending');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn')->useCurrent();

            $table->primary(['Id'], 'pk__t_compli__3214ec07fbf5abb5');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ComplianceIncidentActions');
    }
};
