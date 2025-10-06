<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_Banks', function (Blueprint $table) {
            $table->id('BankID');
            $table->string('BankName', 200);
            $table->string('ShortName', 50)->nullable();
            $table->string('BankCode', 50)->nullable();
            $table->string('SwiftCode', 20)->nullable();
            $table->string('ClearingCode', 50)->nullable();
            $table->bigInteger('CountryID')->nullable();
            $table->string('EmailID', 150)->nullable();
            $table->string('Phone', 50)->nullable();
            $table->string('Website', 150)->nullable();
            $table->boolean('IsActive')->default(1);
            $table->dateTime('CreatedOn')->default(DB::raw('GETDATE()'));
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_Banks');
    }
};
