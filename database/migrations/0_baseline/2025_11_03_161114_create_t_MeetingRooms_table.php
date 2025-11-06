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
        Schema::create('t_MeetingRooms', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('RoomID')->unique();
            $table->string('Name', 200);
            $table->smallInteger('Capacity');
            $table->text('Extra')->nullable();
            $table->text('Notes')->nullable();
            $table->string('BranchId', 5)->nullable()->index();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_meetin__3214ec077f28a3c4');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_MeetingRooms');
    }
};
