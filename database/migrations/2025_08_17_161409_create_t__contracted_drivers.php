<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_ContractedDrivers', function (Blueprint $table) {
            $table->id('Id');
            $table->string('DriverNo')->unique();
            $table->string('FullName');
            $table->string('NationalID')->nullable();
            $table->string('Phone')->nullable();
            $table->string('CompanyName')->nullable();
            $table->date('ContractStartDate')->nullable();
            $table->date('ContractEndDate')->nullable();
            $table->string('LicenseNumber')->nullable();
            $table->string('Notes')->nullable();
            $table->boolean('IsActive')->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->nullable()->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn')->nullable();
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ContractedDrivers');
    }
};
