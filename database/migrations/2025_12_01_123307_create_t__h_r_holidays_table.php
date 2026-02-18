// database/migrations/2025_12_01_000010_create_t_HRHolidays_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_HRHolidays', function (Blueprint $table) {
            $table->id('Id');
            $table->string('Name', 150);
            $table->date('HolidayDate');
            $table->string('Region', 100)->nullable();   // National, Branch-specific, etc.
            $table->boolean('IsRecurring')->default(0);  // repeats every year (same month/day)
            $table->boolean('IsActive')->default(1);

            $table->string('Status', 20)->default('Pending');
            // Pending, Approved, Rejected (for “validation” step in doc)

            $table->unsignedBigInteger('CreatedBy');
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('ApprovedBy')->nullable();
            $table->dateTime('ApprovedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HRHolidays');
    }
};
