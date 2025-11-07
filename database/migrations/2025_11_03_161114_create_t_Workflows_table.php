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
        Schema::create('t_Workflows', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('Name', 100)->index();
            $table->string('Source', 100);
            $table->string('FinalStage', 100);
            $table->text('Description')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->boolean('IsFinalStage')->nullable();

            $table->primary(['Id'], 'pk__t_workfl__3214ec07bc32a5b3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Workflows');
    }
};
