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
        Schema::create('t_RFQClarifications', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('RFQId');
            $table->bigInteger('SupplierId');
            $table->bigInteger('RFQLineId')->nullable();
            $table->text('Question');
            $table->text('Answer')->nullable();
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_rfqcla__3214ec07c5df1e9c');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_RFQClarifications');
    }
};
