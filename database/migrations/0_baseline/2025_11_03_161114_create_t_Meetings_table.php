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
        Schema::create('t_Meetings', function (Blueprint $table) {
            $table->bigIncrements('MeetingID');
            $table->string('Title', 200);
            $table->dateTime('StartOn');
            $table->dateTime('EndOn');
            $table->string('Type');
            $table->string('Location', 200);
            $table->text('Notes')->nullable();
            $table->char('StatusID', 2);
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->char('MeetingLocationType', 2)->default('ph');
            $table->bigInteger('LocationId')->nullable();
            $table->string('Source', 100)->nullable();
            $table->string('SourceID', 100)->nullable();

            $table->primary(['MeetingID'], 'pk__t_meetin__e9f9e9acc28e2736');
            $table->index(['Source', 'SourceID']);
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
