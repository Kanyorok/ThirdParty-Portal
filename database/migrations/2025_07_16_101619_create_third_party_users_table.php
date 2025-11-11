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

            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->foreign('CreatedBy')->references('Id')->on('t_ThirdPartyUsers')->onDelete('no action');

            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->foreign('ModifiedBy')->references('Id')->on('t_ThirdPartyUsers')->onDelete('no action');

            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->foreign('DeletedBy')->references('Id')->on('t_ThirdPartyUsers')->onDelete('no action');

            // FIX: Explicitly add custom timestamp columns
            $table->timestamp('CreatedOn')->useCurrent();
            $table->timestamp('ModifiedOn')->useCurrent()->useCurrentOnUpdate();

            $table->softDeletes('DeletedOn'); // This is correct for the soft delete column
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_ThirdPartyUsers');
    }
};
