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
        Schema::create('t_LegalCaseCounsels', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('LegalCaseID');
            $table->string('CounselName');
            $table->string('FirmName');
            $table->string('Email')->unique();
            $table->string('Phone')->unique();
            $table->string('Role')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_legalc__3214ec07811f464e');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_LegalCaseCounsels');
    }
};
