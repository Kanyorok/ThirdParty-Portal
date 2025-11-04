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
        Schema::create('t_Schedule', static function (Blueprint $table) {
            $table->id('ScheduleID');
            $table->string('Title', '200');
            $table->longText('Notes');
            $table->string("ScheduledType")->nullable();
            $table->unsignedBigInteger("ScheduledTypeID")->nullable();
            $table->index(["ScheduledType", "ScheduledTypeID"]);
            $table->char('ScheduleStatusID', '2')->default(\App\Enums\ScheduleStatusEnum::Scheduled->value);
            $table->dateTime('StartOn');
            $table->dateTime('EndOn');
            $table->string('Type');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Schedule');
    }
};
