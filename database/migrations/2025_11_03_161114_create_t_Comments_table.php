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
        Schema::create('t_Comments', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->text('Notes');
            $table->string('CommentType');
            $table->string('CommentTypeID', 100);
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->string('RemoteId')->nullable();
            $table->string('Source')->nullable();
            $table->text('Response')->nullable();

            $table->primary(['Id'], 'pk__t_commen__3214ec07cd24e8c5');
            $table->index(['CommentType', 'CommentTypeID']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Comments');
    }
};
