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
        Schema::create('t_ThirdPartiesBankDetails', function (Blueprint $table) {
            $table->bigIncrements('BankID');
            $table->string('BankName')->nullable();
            $table->string('Branch')->nullable();
            $table->string('AccountNumber')->nullable();
            $table->string('CurrencyId')->nullable();
            $table->string('SwiftCode')->nullable();
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('ThirdPartyId');

            $table->primary(['BankID'], 'pk__t_thirdp__aa08cb33c142585a');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ThirdPartiesBankDetails');
    }
};
