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
        Schema::create('t_Tasks', function (Blueprint $table) {
            $table->bigIncrements('TaskID');
            $table->string('Party');
            $table->string('PartyID', 100);
            $table->bigInteger('UserID');
            $table->dateTime('Dated');
            $table->dateTime('CompletedOn')->nullable();
            $table->text('Notes')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->string('Source', 100)->nullable();
            $table->string('SourceID', 100)->nullable();

            $table->primary(['TaskID'], 'pk__t_tasks__7c6949d143fb9f94');
            $table->index(['Party', 'PartyID']);
            $table->index(['Source', 'SourceID']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Tasks');
    }
};
