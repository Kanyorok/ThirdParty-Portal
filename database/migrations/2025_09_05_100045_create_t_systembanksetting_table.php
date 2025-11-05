<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_SystemBankSetting', function (Blueprint $table) {
            $table->id('Id');
            $table->string('BankName', 200);
            $table->string('ShortName', 50)->nullable();
            $table->string('BankCode', 50)->nullable();
            $table->string('SwiftCode', 20)->nullable();
            $table->string('ClearingCode', 50)->nullable();
            $table->string('Address1', 200)->nullable();
            $table->string('Address2', 200)->nullable();
            $table->bigInteger('CityID')->nullable();
            $table->bigInteger('CountryID')->nullable();
            $table->string('ZipCode', 20)->nullable();
            $table->string('Phone1', 50)->nullable();
            $table->string('Phone2', 50)->nullable();
            $table->string('Mobile', 50)->nullable();
            $table->string('Fax', 50)->nullable();
            $table->string('EmailID', 150)->nullable();
            $table->string('Website', 150)->nullable();
            $table->string('BankRegNumber', 100)->nullable();
            $table->date('AuditedDate')->nullable();
            $table->bigInteger('BankTypeID')->nullable();
            $table->bigInteger('ImageID')->nullable();
            $table->boolean('IsActive')->default(1);
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->default(DB::raw('GETDATE()'));
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('SupervisedBy')->nullable();
            $table->dateTime('SupervisedOn')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_SystemBankSetting');
    }
};
