<?php

use App\Enums\Core\VisibilityEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_DocumentValidationTypes', static function (Blueprint $table) {
            $table->id('Id');
            $table->string('ValidationTypeId', 200)->unique();
            $table->string('Name');
            $table->longText('Notes')->nullable();
            $table->char('Visibility', 3)->default(VisibilityEnum::Private->value);
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        Schema::create('t_DocumentValidations', static function (Blueprint $table) {
            $table->id('Id');
            $table->string('ValidationId', 200)->unique();
            $table->string('Name');
            $table->char('Visibility', 3)->default(VisibilityEnum::Private->value);
            $table->foreignId('ValidationTypeId')->nullable()->constrained('t_DocumentValidationTypes', 'Id');
            $table->foreignId('DocumentId')->nullable()->constrained('t_Documents', 'Id');
            $table->foreignId('ApprovedBy')->nullable()->constrained('t_Users', 'Id');
            $table->dateTime('ApprovedOn')->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        Schema::create('t_DocumentValidationAttributes', static function (Blueprint $table) {
            $table->id('Id');
            $table->string('Name');
            $table->text('Value');
            $table->char('DataType', 2);
            $table->foreignId('DocumentValidationId')->constrained('t_DocumentValidations', 'Id');
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
        Schema::dropIfExists('t_DocumentValidationAttributes');
        Schema::dropIfExists('t_DocumentValidations');
        Schema::dropIfExists('t_DocumentValidationTypes');
    }
};
