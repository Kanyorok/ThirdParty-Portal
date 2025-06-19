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
        Schema::create('t_Modules', static function (Blueprint $table) {
            $table->unsignedBigInteger('ModuleID')->primary();
            $table->string('Name', 100);
            $table->string('Icon', 100)->nullable();
            $table->string('Description', 200)->nullable();
            $table->string('Route', 200)->nullable();

            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        Schema::table('t_Modules', static function (Blueprint $table) {
            $table->foreignId('ParentID')->nullable()->constrained('t_Modules', 'ModuleID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Modules');
    }
};
