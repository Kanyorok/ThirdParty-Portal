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
        Schema::create('t_DMSSignatures', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('SignatureId', 200)->unique();
            $table->string('Name');
            $table->text('Description')->nullable();
            $table->char('Visibility', 3)->default('pri');
            $table->bigInteger('ImageId')->nullable();
            $table->integer('SignatureHorizontalStart')->default(10);
            $table->integer('SignatureVerticalStart')->default(10);
            $table->smallInteger('SignatureOpacity')->default(100);
            $table->smallInteger('SignatureWidth')->default(500);
            $table->smallInteger('SignatureHeight')->default(500);
            $table->string('Content');
            $table->string('ContentColour')->default('red');
            $table->string('ContentSize')->default('28');
            $table->char('ContentPosition', 2)->default('cc');
            $table->string('ContentBorderColour')->default('black');
            $table->smallInteger('ContentBorderWeight')->default(0);
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_dmssig__3214ec07db517170');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_DMSSignatures');
    }
};
