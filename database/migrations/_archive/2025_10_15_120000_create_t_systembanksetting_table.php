<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('t_SystemBankSetting')) {
            Schema::create('t_SystemBankSetting', function (Blueprint $table) {
                $table->bigIncrements('Id');
                $table->string('BankName', 200);
                $table->string('ShortName', 50)->nullable();
                $table->string('BankCode', 50)->nullable();
                $table->string('SwiftCode', 20)->nullable();
                $table->string('ClearingCode', 50)->nullable();
                $table->string('Address1', 200)->nullable();
                $table->string('Address2', 200)->nullable();
                $table->unsignedBigInteger('CityID')->nullable();
                $table->unsignedBigInteger('CountryID')->nullable();
                $table->string('ZipCode', 20)->nullable();
                $table->string('Phone1', 50)->nullable();
                $table->string('Phone2', 50)->nullable();
                $table->string('Mobile', 50)->nullable();
                $table->string('Fax', 50)->nullable();
                $table->string('EmailID', 150)->nullable();
                $table->string('Website', 150)->nullable();
                $table->string('BankRegNumber', 100)->nullable();
                $table->date('AuditedDate')->nullable();
                $table->unsignedBigInteger('BankTypeID')->nullable();
                $table->unsignedBigInteger('ImageID')->nullable();
                $table->boolean('IsActive')->default(true);
                $table->unsignedBigInteger('CreatedBy')->nullable();
                $table->dateTime('CreatedOn')->useCurrent();
                $table->unsignedBigInteger('ModifiedBy')->nullable();
                $table->dateTime('ModifiedOn')->nullable();
                $table->unsignedBigInteger('SupervisedBy')->nullable();
                $table->dateTime('SupervisedOn')->nullable();

                // FKs (soft), keep nullable to avoid breaking installs
                if (Schema::hasTable('t_Countries')) {
                    $table->foreign('CountryID')->references('Id')->on('t_Countries');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('t_SystemBankSetting');
    }
};

