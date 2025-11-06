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
        Schema::create('t_ThirdPartyType_ThirdParties', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('TypeId');
            $table->bigInteger('ThirdPartyId');
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_thirdp__3214ec077e97a3c6');
            $table->unique(['TypeId', 'ThirdPartyId']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ThirdPartyType_ThirdParties');
    }
};
