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
        Schema::create('t_MarketingListsFilters', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('MarketingListId');
            $table->bigInteger('FilterId');
            $table->string('FilterValue')->nullable();
            $table->text('FilterValues')->nullable();
            $table->enum('After', ['and', 'or'])->default('and');
            $table->integer('DisplayOrder');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_market__3214ec07fb893299');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_MarketingListsFilters');
    }
};
