<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_MeetingRooms', static function (Blueprint $table) {
            $table->id('Id');
            $table->string('RoomID')->unique();
            $table->string('Name', 200);
            $table->unsignedSmallInteger('Capacity');
            $table->jsonb('Extra')->nullable();
            $table->longText('Notes')->nullable();
            $table->string('BranchId', '5')->nullable()->index();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        Schema::table('t_Meetings', static function (Blueprint $table) {
            $table->foreignId('LocationId')->nullable()->after('Location')->constrained('t_MeetingRooms', 'Id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_Meetings', static function (Blueprint $table) {
            $table->dropConstrainedForeignId('LocationId');
        });

        Schema::dropIfExists('t_MeetingRooms');
    }
};
