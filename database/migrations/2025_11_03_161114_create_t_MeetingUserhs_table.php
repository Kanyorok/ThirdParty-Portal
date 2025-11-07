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
        Schema::create('t_MeetingUserhs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('UserID');
            $table->bigInteger('MeetingId');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');

            $table->primary(['id'], 'pk__t_meetin__3213e83f2b7454ba');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_MeetingUserhs');
    }
};
