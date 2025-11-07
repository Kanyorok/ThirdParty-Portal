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
        Schema::create('t_EmailImage', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('EmailId');
            $table->bigInteger('ImageId');
            $table->dateTime('CreatedOn');
            $table->dateTime('ModifiedOn');

            $table->primary(['Id'], 'pk__t_emaili__3214ec079fd9e170');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_EmailImage');
    }
};
