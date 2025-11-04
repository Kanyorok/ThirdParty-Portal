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
        Schema::create('t_TenderSection', function (Blueprint $table) {
            $table->id();
            $table->foreignId('SectionID')->constrained('t_Sections', 'Id'); // FK to t_Sections
            $table->foreignId('TenderID')->constrained('t_Tenders', 'Id'); // FK to t_Tenders
            $table->decimal('Weight', 5, 2)->default(0.00); // DECIMAL(5,2)
            $table->boolean('IsActive')->default(true); // BIT (boolean in Laravel)
            $table->text('Comments')->nullable(); // TEXT (optional)

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
        Schema::dropIfExists('t_TenderSection');
    }
};
