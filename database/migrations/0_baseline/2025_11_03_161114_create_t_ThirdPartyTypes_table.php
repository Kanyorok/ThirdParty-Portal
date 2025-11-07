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
        Schema::create('t_ThirdPartyTypes', function (Blueprint $table) {
            $table->bigIncrements('TypeId');
            $table->bigInteger('Type')->index();
            $table->bigInteger('FinanceRole')->nullable()->index();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->string('Code', 30)->nullable()->unique();

            $table->primary(['TypeId'], 'pk__t_thirdp__516f03b5497d2f98');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ThirdPartyTypes');
    }
};
