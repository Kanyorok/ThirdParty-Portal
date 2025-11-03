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
        Schema::create('t_ThirdPartyUsers', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('UserID')->unique();
            $table->string('FirstName')->nullable();
            $table->string('LastName')->nullable();
            $table->string('Email')->nullable()->unique();
            $table->string('Phone')->nullable();
            $table->integer('ImageId')->nullable();
            $table->string('Gender')->nullable();
            $table->boolean('IsActive')->default(true);
            $table->dateTime('EmailVerifiedOn')->nullable();
            $table->string('Password');
            $table->string('remember_token', 100)->nullable();
            $table->bigInteger('CreatedBy')->nullable();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->dateTime('ModifiedOn')->useCurrent();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('ThirdPartyId')->nullable();

            $table->primary(['Id'], 'pk__t_thirdp__3214ec07b473f6e0');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ThirdPartyUsers');
    }
};
