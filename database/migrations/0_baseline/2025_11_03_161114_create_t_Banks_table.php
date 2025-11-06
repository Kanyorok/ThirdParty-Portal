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
        Schema::create('t_Banks', function (Blueprint $table) {
            $table->bigIncrements('BankID');
            $table->string('BankName', 200);
            $table->string('ShortName', 50)->nullable();
            $table->string('BankCode', 50)->nullable();
            $table->string('SwiftCode', 20)->nullable();
            $table->string('ClearingCode', 50)->nullable();
            $table->bigInteger('CountryID')->nullable();
            $table->string('EmailID', 150)->nullable();
            $table->string('Phone', 50)->nullable();
            $table->string('Website', 150)->nullable();
            $table->boolean('IsActive')->default(true);
            $table->dateTime('CreatedOn')->useCurrent();
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();

            $table->primary(['BankID'], 'pk__t_banks__aa08cb33e5be2785');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Banks');
    }
};
