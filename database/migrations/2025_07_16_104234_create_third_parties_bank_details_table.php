<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_ThirdPartiesBankDetails', function (Blueprint $table) {
            $table->id('BankID');
            $table->string('BankName')->nullable();
            $table->string('Branch')->nullable();
            $table->string('AccountNumber')->nullable();
            $table->string('CurrencyId')->nullable();
            $table->string('SwiftCode')->nullable();

            $table->foreignId('CreatedBy')->nullable()->constrained('t_ThirdPartyUsers', 'Id');
            $table->timestamp('CreatedOn')->nullable();
            $table->foreignId('ModifiedBy')->nullable()->constrained('t_ThirdPartyUsers', 'Id');
            $table->timestamp('ModifiedOn')->nullable();
            $table->foreignId('DeletedBy')->nullable()->constrained('t_ThirdPartyUsers', 'Id');
            $table->softDeletes('DeletedOn');

            $table->foreignId('ThirdPartyId')->constrained('t_ThirdParties', 'Id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_ThirdPartiesBankDetails');
    }
};
