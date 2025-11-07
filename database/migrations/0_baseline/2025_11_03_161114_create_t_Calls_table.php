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
        Schema::create('t_Calls', function (Blueprint $table) {
            $table->bigIncrements('CallID');
            $table->bigInteger('ScheduleID')->nullable();
            $table->string('Party');
            $table->string('PartyID', 100);
            $table->bigInteger('UserID');
            $table->dateTime('StartOn');
            $table->dateTime('EndOn')->nullable();
            $table->text('Notes')->nullable();
            $table->char('CallTypeID', 2)->default('ou');
            $table->char('CallStatusID', 2);
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->string('Source', 100)->nullable();
            $table->string('SourceID', 100)->nullable();
            $table->text('Response')->nullable();

            $table->primary(['CallID'], 'pk__t_calls__5180cf8a199dd068');
            $table->index(['Party', 'PartyID']);
            $table->index(['Source', 'SourceID']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Calls');
    }
};
