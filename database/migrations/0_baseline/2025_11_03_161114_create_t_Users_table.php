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
        Schema::create('t_Users', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('UserID')->unique();
            $table->string('Name');
            $table->string('Email')->unique();
            $table->string('Phone')->unique();
            $table->boolean('Linked');
            $table->text('Notes')->nullable();
            $table->text('Email_Signature')->nullable();
            $table->string('Password');
            $table->string('remember_token', 100)->nullable();
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('ImageId')->nullable();
            $table->string('ClientID')->nullable()->index();
            $table->string('ExtensionNo', 50)->nullable()->index();
            $table->bigInteger('EmployeeId')->nullable()->unique();
            $table->boolean('IsActive')->default(true);
            $table->string('current_session_id')->nullable()->index();
            $table->bigInteger('session_version')->default(1)->index();
            $table->dateTime('last_login_at')->nullable();

            $table->primary(['Id'], 'pk__t_users__3214ec07bfa983bb');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Users');
    }
};
