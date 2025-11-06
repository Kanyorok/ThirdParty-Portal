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
        Schema::create('t_PartyActivities', function (Blueprint $table) {
            $table->bigIncrements('ActivityID');
            $table->string('Party');
            $table->string('PartyID', 100);
            $table->bigInteger('UserID');
            $table->text('Notes');
            $table->string('ActivityType')->nullable();
            $table->string('ActivityTypeID', 100)->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['ActivityID'], 'pk__t_partya__45f4a7f1d8a9a8b0');
            $table->index(['ActivityType', 'ActivityTypeID']);
            $table->index(['Party', 'PartyID']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_PartyActivities');
    }
};
