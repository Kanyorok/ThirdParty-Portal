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
    Schema::create('t_ComplianceCalendarEntries', function (Blueprint $table) {
        $table->id('Id');
        $table->foreignId('ObligationID')->nullable()->constrained('t_ComplianceObligations', 'Id')->nullOnDelete();
        $table->string('Title', 200);
        $table->text('Description')->nullable();
        $table->date('StartDate');
        $table->date('EndDate')->nullable();
        $table->unsignedBigInteger('OwnerID')->nullable(); // user responsible
        $table->boolean('IsRecurring')->default(0);
        $table->string('RecurrenceType', 50)->nullable(); // e.g., Monthly, Quarterly

        $table->unsignedBigInteger('CreatedBy');
        $table->timestamp('CreatedOn')->useCurrent();
        $table->unsignedBigInteger('ModifiedBy')->nullable();
        $table->timestamp('ModifiedOn')->nullable();
        $table->unsignedBigInteger('DeletedBy')->nullable();
        $table->timestamp('DeletedOn')->nullable();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
     Schema::dropIfExists('t_ComplianceCalendarEntries');
    }
};
