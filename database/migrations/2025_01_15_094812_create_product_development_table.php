<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_ProductDevelopment', static function (Blueprint $table) {
            $table->id('Id');
            $table->string('ProductID', 100)->unique();
            $table->string('Name');
            $table->string('TargetGroup');
            $table->foreignId('User_ID')->nullable();
            $table->decimal('Income', 25, 2)->nullable();
            $table->decimal('Revenue', 25, 2)->nullable();
            $table->longText('Regulatory')->nullable();
            $table->longText('Notes')->nullable();
            $table->longText('Justification')->nullable();
            $table->longText('Risks')->nullable();
            $table->longText('RiskStrategies')->nullable();
            $table->longText('Summary')->nullable();
            $table->foreignId('StageId')->constrained('t_CRMCodeDetails', 'ID')->cascadeOnDelete();
            $table->foreignId('ArchivedBy')->nullable()->constrained('t_Users', 'Id');
            $table->dateTime('ArchivedOn')->nullable();
            $table->dateTime('CommentStart')->nullable();
            $table->dateTime('CommentEnd')->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        Schema::create('t_ProductDevelopmentImages', static function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('ProductDevelopmentId')->constrained('t_ProductDevelopment', 'Id');
            $table->foreignId('ImageId')->constrained('t_CRMImages', 'ImageID');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
        });

        Schema::create('t_ProductDevelopmentFeatures', static function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('ProductDevelopmentId')->constrained('t_ProductDevelopment', 'Id');
            $table->string('Feature');
            $table->longText('Description');
            $table->longText('Notes')->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_ProductDevelopmentFeatures');
        Schema::dropIfExists('t_ProductDevelopmentImages');
        Schema::dropIfExists('t_ProductDevelopment');
    }
};
