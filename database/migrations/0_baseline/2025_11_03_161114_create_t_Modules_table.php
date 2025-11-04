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
        Schema::create('t_Modules', function (Blueprint $table) {
            $table->bigInteger('ModuleID');
            $table->string('Name', 100);
            $table->string('Icon', 100)->nullable();
            $table->string('Description', 200)->nullable();
            $table->string('Route', 200)->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('ParentID')->nullable();
            $table->string('ModuleKey', 32)->nullable()->index();

            $table->primary(['ModuleID']);
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
