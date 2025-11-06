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
        Schema::create('t_Images', function (Blueprint $table) {
            $table->bigIncrements('ImageID');
            $table->string('Name', 250)->nullable();
            $table->string('ImageType')->nullable();
            $table->bigInteger('ImageTypeID')->nullable();
            $table->text('Image');
            $table->string('MIMEType', 200)->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['ImageID'], 'pk__t_images__7516f4ec3b948b2f');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Images');
    }
};
