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
        Schema::create('t_FileFormats', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('Name', 50);
            $table->string('MimeType', 100)->nullable();
            $table->boolean('IsActive')->default(true);
            $table->timestamps();

            $table->primary(['Id'], 'pk__t_filefo__3214ec07b69e3bf5');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_FileFormats');
    }
};
