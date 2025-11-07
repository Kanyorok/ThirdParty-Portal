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
        Schema::table('t_Notes', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DiscussionID'])->references(['DiscussionID'])->on('t_Discussions')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_Notes', function (Blueprint $table) {
            $table->dropForeign('t_notes_createdby_foreign');
            $table->dropForeign('t_notes_discussionid_foreign');
            $table->dropForeign('t_notes_modifiedby_foreign');
        });
    }
};
