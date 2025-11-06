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
        Schema::create('t_LeadUsers', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('LeadId');
            $table->string('Party');
            $table->string('PartyID', 100);
            $table->char('Role', 1)->default('r');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_leadus__3214ec07696b77f0');
            $table->index(['Party', 'PartyID']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_LeadUsers');
    }
};
