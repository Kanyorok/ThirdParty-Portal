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
        Schema::create('t_PartyActivities', static function (Blueprint $table) {
            $table->id('ActivityID');
            $table->string("Party");
            $table->string("PartyID", 100);
            $table->foreignId('UserID')->constrained('t_Users', 'Id');
            $table->longText('Notes');
            $table->string("ActivityType")->nullable();
            $table->string("ActivityTypeID", 100)->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');

            $table->index(["ActivityType", "ActivityTypeID"]);
            $table->index(["Party", "PartyID"]);
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
