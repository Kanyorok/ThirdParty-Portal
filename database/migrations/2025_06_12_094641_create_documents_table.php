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
        Schema::create('t_Repositories', static function (Blueprint $table) {//base repository
            $table->id('Id');
            $table->string('Name');
            $table->longText('Description')->nullable();
            $table->foreignId('RepositoryId')->nullable()->constrained('t_Repositories', 'Id');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        Schema::create('t_Documents', static function (Blueprint $table) {
            $table->id('Id');
            $table->string('Name');
            $table->string("ImageType")->nullable();
            $table->foreignId('CategoryId')->constrained('t_CategoryMaster', 'Id');
            $table->foreignId('RepositoryId')->constrained('t_Repositories', 'Id');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        Schema::create('t_DocumentVersions', static function (Blueprint $table) {
            $table->id('Id');
            $table->string('Name');
            $table->string('Version');
            $table->string('Path', 2000);
            $table->string('Disk', 100);
            $table->string('Checksum', 100);
            $table->unsignedBigInteger('Size');
            $table->longText('Description')->nullable();
            $table->foreignId('DocumentId')->constrained('t_Documents', 'Id');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        Schema::create('t_DocumentAttributes', static function (Blueprint $table) {
            $table->id('Id');
            $table->string('Name');
            $table->string('Value');
            $table->char('DataType', 2);
            $table->foreignId('DocumentId')->constrained('t_Documents', 'Id');
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
        Schema::dropIfExists('t_DocumentAttributes');
        Schema::dropIfExists('t_DocumentVersions');
        Schema::dropIfExists('t_Documents');
        Schema::dropIfExists('t_Repositories');
    }
};
