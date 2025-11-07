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
        Schema::create('t_SpecialPermissions', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->char('Permission', 1)->default('r');
            $table->string('Party');
            $table->string('PartyID', 100);
            $table->string('Model');
            $table->string('ModelID', 100);
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_specia__3214ec07871d0a6a');
            $table->index(['Model', 'ModelID']);
            $table->index(['Party', 'PartyID']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_SpecialPermissions');
    }
};
