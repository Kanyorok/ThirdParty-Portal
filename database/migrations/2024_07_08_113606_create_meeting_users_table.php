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
        if (!Schema::hasTable('t_MeetingUsers')) {
            Schema::create('t_MeetingUserhs', static function (Blueprint $table) {
                $table->id();
                $table->foreignId('UserID')->constrained('t_Users', 'Id');
                $table->foreignId('MeetingId')->constrained('t_Meetings', 'MeetingID');
                $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
                $table->dateTime('CreatedOn');
                $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
                $table->dateTime('ModifiedOn');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_MeetingUsers');
    }
};
