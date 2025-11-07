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
        Schema::create('t_ItemSubCategories', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('SubCategoryCode')->unique();
            $table->string('SubCategoryName');
            $table->text('Description')->nullable();
            $table->boolean('Status')->default(true);
            $table->bigInteger('ParentCategory');
            $table->dateTime('CreatedOn')->useCurrent();
            $table->dateTime('ModifiedOn')->useCurrent();
            $table->dateTime('DeletedOn')->useCurrent();

            $table->primary(['Id'], 'pk__t_itemsu__3214ec0771538670');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ItemSubCategories');
    }
};
