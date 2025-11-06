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
        Schema::create('t_ItemCategories', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('Name');
            $table->string('Description')->nullable();
            $table->bigInteger('ParentId')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->string('CategoryCode')->unique();
            $table->bigInteger('Status')->nullable();

            $table->primary(['Id'], 'pk__t_itemca__3214ec078154f893');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ItemCategories');
    }
};
