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
        Schema::create('t_Stores', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('StoreID')->nullable()->unique();
            $table->string('StoreName');
            $table->bigInteger('BranchID');
            $table->string('Status');
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->boolean('IsMainStore')->default(false);

            $table->primary(['Id'], 'pk__t_stores__3214ec0740fc1d38');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Stores');
    }
};
