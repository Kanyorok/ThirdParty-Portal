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
        Schema::create('t_Criterias', function (Blueprint $table) {
            $table->Id();
            $table->foreignId('SectionID')->constrained('t_Sections', 'Id')->onDelete('cascade');    // INT (FK)
            $table->string('CriteriaName', 100);      // VARCHAR(100)
            $table->text('Description')->nullable();  // TEXT
            $table->boolean('IsActive')->default(true); // BIT (boolean in Laravel)


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
        Schema::dropIfExists('t_Criterias');
    }
};
