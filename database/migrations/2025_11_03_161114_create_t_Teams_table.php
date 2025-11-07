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
        Schema::create('t_Teams', function (Blueprint $table) {
            $table->bigIncrements('TeamID');
            $table->string('Name');
            $table->string('Email');
            $table->text('Notes')->nullable();
            $table->boolean('IsMarketing')->default(false);
            $table->bigInteger('UserId')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['TeamID'], 'pk__t_teams__123ae7b99fdc1871');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Teams');
    }
};
