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
        Schema::create('t_Teams', static function (Blueprint $table) {
            $table->id('TeamID');
            $table->string('Name');
            $table->string('Email');
            $table->longText('Notes')->nullable();
            $table->boolean('IsMarketing')->default(false);
            $table->foreignId('UserId')->nullable()->comment('team lead')->constrained('t_Users', 'Id');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        Schema::create('t_TeamUser', static function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('TeamId')->constrained('t_Teams', 'TeamID');
            $table->foreignId('UserId')->constrained('t_Users', 'Id');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_TeamUser');
        Schema::dropIfExists('t_Teams');
    }
};
