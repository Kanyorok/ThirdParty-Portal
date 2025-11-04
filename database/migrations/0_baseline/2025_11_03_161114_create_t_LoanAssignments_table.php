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
        Schema::create('t_LoanAssignments', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('AccountID')->index();
            $table->dateTime('StartOn');
            $table->dateTime('EndOn')->nullable();
            $table->string('Notes', 200)->nullable();
            $table->bigInteger('UserId');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');

            $table->primary(['Id'], 'pk__t_loanas__3214ec078e15575e');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_LoanAssignments');
    }
};
