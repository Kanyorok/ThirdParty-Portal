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
        Schema::create('t_CategoryProgressHistory', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('ApplicationCategoryId');
            $table->string('PreviousStatus', 50)->nullable();
            $table->string('NewStatus', 50);
            $table->decimal('PreviousProgress', 5)->default(0);
            $table->decimal('NewProgress', 5);
            $table->bigInteger('ChangedBy')->nullable();
            $table->text('Notes')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn')->useCurrent();

            $table->primary(['Id'], 'pk__t_catego__3214ec078f605d5d');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_CategoryProgressHistory');
    }
};
