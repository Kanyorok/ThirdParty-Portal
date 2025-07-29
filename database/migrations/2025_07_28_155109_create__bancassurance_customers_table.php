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
        Schema::create('t_BancassuranceCustomers', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('ReferralID')->constrained('t_BancassuranceReferrals','Id')->nullable;
            $table->string('FullName');
            $table->string('NationalID')->unique();
            $table->string('KRAPIN')->unique();
            $table->date('DateOfBirth');
            $table->foreignId('Gender')->constrained('t_CodeDetails','ID');
            $table->foreignId('MaritalStatus')->constrained('t_CodeDetails','ID');
            $table->string('PhoneNumber')->unique();
            $table->string('Email')->unique();
            $table->string('Address');
            $table->string('Occupation')->constrained('t_CodeDetails','ID');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_BancassuranceCustomers');
    }
};
