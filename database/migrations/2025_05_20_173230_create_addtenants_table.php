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
        Schema::create('t_TenantMaintenance', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('TenantType')->constrained('t_CodeDetails', 'Id');
            $table->string('TenantName');
            $table->string('IDRegistrationNo')->unique();
            $table->string('PhoneNumber')->unique();
            $table->string('EmailAddress')->unique();
            $table->string('Nationality');
            $table->string('PostalAddress');
            $table->string('Remarks');
            $table->boolean('IsActive')->default(1);
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
        DB::statement("
            DECLARE @sql NVARCHAR(MAX) = '';
            SELECT @sql += 'ALTER TABLE ' + QUOTENAME(OBJECT_NAME(parent_object_id)) +
            ' DROP CONSTRAINT ' + QUOTENAME(name) + ';'
            FROM sys.foreign_keys
            WHERE referenced_object_id = OBJECT_ID('t_TenantMaintenance');
            EXEC sp_executesql @sql;
        ");
        Schema::dropIfExists('t_TenantMaintenance');

    }

};