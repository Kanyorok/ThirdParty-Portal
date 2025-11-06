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
        Schema::create('t_Users', static function (Blueprint $table) {
            $table->id('Id');
            $table->string('UserID')->unique()->comment('from br Operator Id');
            $table->string('Name');
            $table->string('Email')->unique();
            $table->string('Phone')->unique();
            $table->boolean('Linked');
            $table->char('Gender', 1)->default('o');//enum
            $table->longText('Notes')->nullable();
            $table->longText('Email_Signature')->nullable();
            $table->char('BranchId', '5')->index();
            $table->string('Password');
            $table->rememberToken();
            $table->foreignId('CreatedBy')->nullable()->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn')->nullable();
            $table->foreignId('ModifiedBy')->nullable()->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn')->nullable();
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        Schema::create('t_SYSPasswordResetTokens', static function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('t_SYSSessions', static function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained('t_Users', 'Id');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_SYSPasswordResetTokens');
        Schema::dropIfExists('t_SYSSessions');
        Schema::dropIfExists('t_Users');
    }
};
