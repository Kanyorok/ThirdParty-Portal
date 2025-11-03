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
        Schema::create('t_BoardMembers', static function (Blueprint $table) {
            $table->id('Id');
            $table->string('BoardMemberID')->unique();
            $table->string('Name', 200);
            $table->string('ClientID', 70)->index();
            $table->string('Email', 200);
            $table->string('Phone', 200)->nullable();
            $table->string('Role', 200)->nullable();
            $table->jsonb('Extra')->nullable();
            $table->longText('Notes')->nullable();
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
        Schema::dropIfExists('t_BoardMembers');
    }
};
