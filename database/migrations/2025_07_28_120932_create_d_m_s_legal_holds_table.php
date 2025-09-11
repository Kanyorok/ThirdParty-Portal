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
        Schema::create('t_DMSLegalHolds', static function (Blueprint $table) {
            $table->id('Id');
            $table->string('Name');
            $table->string('Ref', 100)->unique();
            $table->longText('Description');
            $table->char('Status', '1')->default('A');
            $table->foreignId('ReleasedBy')->nullable()->constrained('t_Users', 'Id');
            $table->dateTime('ReleasedOn')->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        Schema::create('t_DocumentLegalHolds', static function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('DocId')->constrained('t_Documents', 'Id');
            $table->foreignId('LegalHoldId')->constrained('t_DMSLegalHolds', 'Id');
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
        Schema::dropIfExists('t_DocumentLegalHolds');
        Schema::dropIfExists('t_DMSLegalHolds');
    }
};
