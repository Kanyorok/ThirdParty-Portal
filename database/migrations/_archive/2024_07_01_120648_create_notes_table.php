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
        Schema::create('t_Notes', static function (Blueprint $table) {
            $table->id('NoteID');
            $table->foreignId('DiscussionID')->nullable()->constrained('t_Discussions', 'DiscussionID');
            $table->string("Party");
            $table->string("PartyID", 100);
            $table->longText('Notes');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');

            $table->index(["Party", "PartyID"]);
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
