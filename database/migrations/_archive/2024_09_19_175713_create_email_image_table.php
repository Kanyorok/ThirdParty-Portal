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
        Schema::create('t_EmailImage', static function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('EmailId')->constrained('t_Emails', 'EmailID');
            $table->foreignId('ImageId')->constrained('t_Images', 'ImageID');
            $table->dateTime('CreatedOn');
            $table->dateTime('ModifiedOn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_EmailImage');
    }
};
