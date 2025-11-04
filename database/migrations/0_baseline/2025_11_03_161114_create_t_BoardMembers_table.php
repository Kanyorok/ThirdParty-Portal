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
        Schema::create('t_BoardMembers', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('BoardMemberID')->unique();
            $table->string('Name', 200);
            $table->string('ClientID', 70)->index();
            $table->string('Email', 200);
            $table->string('Phone', 200)->nullable();
            $table->string('Role', 200)->nullable();
            $table->text('Extra')->nullable();
            $table->text('Notes')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_boardm__3214ec07d3956346');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_BoardMembers');
    }
};
