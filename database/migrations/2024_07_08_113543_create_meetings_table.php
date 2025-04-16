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
        Schema::create('t_Meetings', static function (Blueprint $table) {
            $table->id('MeetingID');
            $table->string('Title', '200');
            $table->dateTime('StartOn');
            $table->dateTime('EndOn');
            $table->string('Type');
            $table->string('Location', 200);
            $table->longText('Notes')->nullable();
            $table->char('StatusID', 2);
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Meetings');
    }
};
