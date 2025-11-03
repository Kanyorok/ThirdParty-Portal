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
        Schema::create('t_Notes', function (Blueprint $table) {
            $table->bigIncrements('NoteID');
            $table->bigInteger('DiscussionID')->nullable();
            $table->string('Party');
            $table->string('PartyID', 100);
            $table->text('Notes');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');

            $table->primary(['NoteID'], 'pk__t_notes__eace357f2c336fdf');
            $table->index(['Party', 'PartyID']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Notes');
    }
};
