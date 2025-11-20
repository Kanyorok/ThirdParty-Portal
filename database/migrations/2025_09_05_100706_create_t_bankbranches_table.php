<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_BankBranches', function (Blueprint $table) {
            $table->id('BranchID');
            $table->unsignedBigInteger('BankID');
            $table->string('BranchName', 200);
            $table->string('BranchCode', 50)->nullable();
            $table->string('Address1', 200)->nullable();
            $table->string('Address2', 200)->nullable();
            $table->bigInteger('CityID')->nullable();
            $table->bigInteger('CountryID')->nullable();
            $table->string('ZipCode', 20)->nullable();
            $table->string('Phone', 50)->nullable();
            $table->string('EmailID', 150)->nullable();
            $table->boolean('IsActive')->default(1);
            $table->dateTime('CreatedOn')->default(DB::raw('GETDATE()'));
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();

            $table->foreign('BankID')->references('BankID')->on('t_Banks');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_BankBranches');
    }
};
