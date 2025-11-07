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
        Schema::create('t_CompetitorProducts', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('CompetitorId');
            $table->string('Name');
            $table->string('Limit')->nullable();
            $table->string('InterestRate')->nullable();
            $table->string('OtherCharges')->nullable();
            $table->string('RepaymentPeriod')->nullable();
            $table->string('SecurityRequired')->nullable();
            $table->bigInteger('Clients')->nullable();
            $table->text('Notes')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_compet__3214ec074ebb007d');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_CompetitorProducts');
    }
};
