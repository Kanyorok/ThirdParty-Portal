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
        Schema::create('t_MeetingBoard', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('BoardMemberId');
            $table->bigInteger('MeetingId');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');

            $table->primary(['Id'], 'pk__t_meetin__3214ec075ac21ed5');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_MeetingBoard');
    }
};
