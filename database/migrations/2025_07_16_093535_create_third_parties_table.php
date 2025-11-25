<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_ThirdParties', function (Blueprint $table) {
            $table->id('Id');

            $table->string('ThirdPartyName')->nullable();
            $table->string('TradingName')->nullable();
            $table->string('BusinessType')->nullable();
            $table->string('RegistrationNumber')->nullable()->unique();
            $table->string('TaxPIN')->nullable();
            $table->string('VATNumber')->nullable();
            $table->string('Country')->nullable();
            $table->string('PhysicalAddress')->nullable();
            $table->string('Email')->nullable()->unique();
            $table->string('Phone')->nullable();
            $table->string('Website')->nullable();
            $table->string('ApprovalStatus')->nullable();
            $table->string('Status')->nullable();
            $table->string('ThirdPartyType');

            $table->foreignId('CreatedBy')->nullable()->constrained('t_Users', 'Id');
            $table->timestamp('CreatedOn')->nullable();
            $table->foreignId('ModifiedBy')->nullable()->constrained('t_Users', 'Id');
            $table->timestamp('ModifiedOn')->nullable();
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_ThirdParties');
    }
};
