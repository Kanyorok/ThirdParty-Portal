<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_ThirdPartyUsers', function (Blueprint $table) {
            $table->id('Id');
            $table->string('UserID')->unique();
            $table->string('FirstName')->nullable();
            $table->string('LastName')->nullable();
            $table->string('Email')->unique()->nullable();
            $table->string('Phone')->nullable();
            $table->integer('ImageId')->nullable();
            $table->string('Gender')->nullable();
            $table->boolean('IsActive')->default(true);
            $table->timestamp('EmailVerifiedOn')->nullable();
            $table->foreignId('ThirdPartyId')->nullable()->constrained('t_ThirdParties', 'Id');
            $table->string('Password');
            $table->rememberToken();
            $table->foreignId('CreatedBy')->nullable()->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn')->nullable();
            $table->foreignId('ModifiedBy')->nullable()->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn')->nullable();
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_ThirdPartyUsers');
    }
};
