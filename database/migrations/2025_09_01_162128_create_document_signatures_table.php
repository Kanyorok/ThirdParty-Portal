<?php

use App\Enums\Core\VisibilityEnum;
use App\Enums\DMS\ImageGravityEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_DocumentSignatures', static function (Blueprint $table) {
            $table->id('Id');
            $table->string('DocumentSignatureId', 200)->unique();
            $table->string('Name');
            $table->longText('Description')->nullable();
            $table->char('Visibility', 3)->default(VisibilityEnum::Private->value)->comment('pub,pri');
            $table->foreignId('SignatureId')->comment('image signature')->constrained('t_Images', 'ImageID');
            $table->integer('SignatureHorizontalStart')->default(10);
            $table->integer('SignatureVerticalStart')->default(10);
            $table->smallInteger('SignatureOpacity')->default(100);
            $table->smallInteger('SignatureWidth')->default(500);
            $table->smallInteger('SignatureHeight')->default(500);
            $table->string('Content');
            $table->string('ContentColour')->default('red');
            $table->string('ContentSize')->default('28');
            $table->char('ContentPosition', 2)->default(ImageGravityEnum::Center->value);
            $table->string('ContentBorderColour')->default('black');
            $table->smallInteger('ContentBorderWeight')->default(0);
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
        Schema::dropIfExists('t_DocumentSignatures');
    }
};
