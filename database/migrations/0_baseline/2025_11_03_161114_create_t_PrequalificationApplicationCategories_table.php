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
        Schema::create('t_PrequalificationApplicationCategories', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('ApplicationID');
            $table->bigInteger('CategoryID');
            $table->dateTime('CreatedOn')->useCurrent();
            $table->dateTime('ModifiedOn')->nullable()->useCurrent();

            $table->primary(['Id'], 'pk__t_prequa__3214ec0728ac4bfd');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_PrequalificationApplicationCategories');
    }
};
