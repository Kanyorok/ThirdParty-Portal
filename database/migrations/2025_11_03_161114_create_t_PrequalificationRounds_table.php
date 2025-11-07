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
        Schema::create('t_PrequalificationRounds', function (Blueprint $table) {
            $table->bigIncrements('RoundID');
            $table->string('Title');
            $table->text('Description')->nullable();
            $table->dateTime('StartDate');
            $table->dateTime('EndDate');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn')->useCurrent();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->integer('MaxVendors')->nullable();

            // Use enum with proper value list (recommended), or use string as alternative
            $table->enum('Status', ['CL', 'O'])->default('O');

            // bigIncrements already creates primary key — no need to call primary() again
            // $table->primary(['RoundID'], 'pk__t_prequa__94d84e1aaeb0b30b');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_PrequalificationRounds');
    }
};
