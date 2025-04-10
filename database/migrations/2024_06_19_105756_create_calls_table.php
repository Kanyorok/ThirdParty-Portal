<?php

use App\Enums\CallTypeEnum;
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
        Schema::create('t_Calls', static function (Blueprint $table) {
            $table->id('CallID');
            $table->foreignId('ScheduleID')->nullable()->constrained('t_Schedule','ScheduleID');
            $table->string("Party");
            $table->string("PartyID", 100);
            $table->foreignId('UserID')->constrained('t_Users', 'Id');
            $table->dateTime('StartOn');
            $table->dateTime('EndOn')->nullable();
            $table->longText('Notes')->nullable();
            $table->char('CallTypeID', 2)->default(CallTypeEnum::Outgoing);
            $table->char('CallStatusID', 2);
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');

            $table->index(["Party", "PartyID"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Calls');
    }
};
