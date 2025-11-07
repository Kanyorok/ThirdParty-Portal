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
        Schema::create('t_TeamUser', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('TeamId');
            $table->bigInteger('UserId');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');

            $table->primary(['Id'], 'pk__t_teamus__3214ec079236e729');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_TeamUser');
    }
};
