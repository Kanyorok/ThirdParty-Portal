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
        Schema::create('t_Countries', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('Name')->unique();
            $table->string('CountryCode', 2)->unique();
            $table->string('PhoneCode', 4)->nullable();
            $table->string('Flag')->nullable();
            $table->bigInteger('CurrencyId');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->tinyInteger('IsActive')->default(1);
            $table->integer('SortOrder')->default(0);
            $table->string('Iso3', 3)->nullable();

            $table->primary(['Id'], 'pk__t_countr__3214ec075bcaada2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Countries');
    }
};
