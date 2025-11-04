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
            $table->bigIncrements('Id');
            $table->bigInteger('ObligationID')->nullable();
            $table->string('Title', 200);
            $table->text('Description')->nullable();
            $table->date('StartDate');
            $table->date('EndDate')->nullable();
            $table->bigInteger('OwnerID')->nullable();
            $table->boolean('IsRecurring')->default(false);
            $table->string('RecurrenceType', 50)->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn')->useCurrent();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_compli__3214ec07b8e0b666');
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
