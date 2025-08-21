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
            $table->id('Id'); // Primary key
            $table->foreignId('LegalCaseID'); // FK to LegalCases
            $table->string('CounselName');
            $table->string('FirmName');
            $table->string('Email')->unique();
            $table->string('Phone')->unique();
            $table->string('Role')->nullable();
            // $table->boolean('IsExternal')->default(false);

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
        Schema::dropIfExists('t_LegalCaseCounsels');
    }
};
