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
        Schema::create('t_Reports', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('Name')->unique();
            $table->text('Description')->nullable();
            $table->string('Path');
            $table->bigInteger('ModuleId');
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->string('ProcedureName', 200)->nullable();

            $table->primary(['Id'], 'pk__t_report__3214ec077a2b0ef6');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Reports');
    }
};
