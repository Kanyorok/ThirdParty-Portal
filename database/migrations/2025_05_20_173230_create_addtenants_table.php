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
        Schema::create('t_AddTenants', function (Blueprint $table) {
            $table->id();
            $table->string('TenantType');
            $table->string('TenantName');
            $table->string('IDRegistrationNo')->unique();
            $table->string('PhoneNumber')->unique();
            $table->string('EmailAddress')->unique();
            $table->string('Nationality');
            $table->string('PostalAddress');
            $table->string('Remarks');
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
        Schema::dropIfExists('t_AddTenants');
    }
};
