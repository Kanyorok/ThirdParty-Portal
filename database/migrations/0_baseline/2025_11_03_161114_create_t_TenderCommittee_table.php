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
        Schema::create('t_TenderCommittee', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('TenderID')->nullable();
            $table->string('CommitteeName', 100)->nullable();
            $table->date('AppointmentDate')->nullable();
            $table->boolean('IsActive')->default(true);
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->string('CommitteeType')->default('tender');
            $table->bigInteger('ReferenceId')->nullable();

            $table->primary(['id'], 'pk__t_tender__3213e83f9de4fce7');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_TenderCommittee');
    }
};
