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
        Schema::create('t_Discussions', function (Blueprint $table) {
            $table->bigIncrements('DiscussionID');
            $table->string('Party');
            $table->string('PartyID', 100);
            $table->string('SourceType')->nullable();
            $table->bigInteger('SourceTypeID')->nullable();
            $table->text('Discussion');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['DiscussionID'], 'pk__t_discus__7e8e3920ff56a1a1');
            $table->index(['Party', 'PartyID']);
            $table->index(['SourceType', 'SourceTypeID']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Discussions');
    }
};
