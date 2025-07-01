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
            $table->string('RepositoryId', 200)->unique();
            $table->string('Name');
            $table->longText('Description')->nullable();
            $table->char('Visibility', 3)->comment('pub,pri');
            $table->foreignId('ParentId')->nullable()->constrained('t_Repositories', 'Id');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        Schema::create('t_Documents', static function (Blueprint $table) {
            $table->id('Id');
            $table->string('DocumentId', 200)->unique();
            $table->string('Name');
            $table->string("MimeType")->nullable();
            $table->foreignId('CategoryId')->nullable()->constrained('t_CategoryMaster', 'Id');
            $table->foreignId('RepositoryId')->constrained('t_Repositories', 'Id');
            $table->char('Visibility', 3)->comment('pub,pri');
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
            $table->char('Disk', 2)->comment('Disk Enum');
            $table->string('Checksum', 100);
            $table->unsignedBigInteger('Size');
            $table->foreignId('DocumentId')->constrained('t_Documents', 'Id');
            $table->longText('Description')->nullable();
            $table->longText('Blob')->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        Schema::create('t_DMSTags', static function (Blueprint $table) {
            $table->id('Id');
            $table->string('TagID', 200)->unique();
            $table->string('Name');
            $table->char('Visibility', 3)->comment('pub,pri');
            $table->longText('Description')->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        Schema::create('t_DocumentTags', static function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('DocId')->constrained('t_Documents', 'Id');
            $table->foreignId('TagId')->nullable()->constrained('t_DMSTags', 'Id');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        Schema::create('t_DMSTaggingRules', static function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('TagId')->nullable()->constrained('t_DMSTags', 'Id');
            $table->char('Content')->comment('ContentEnum - body/title');
            $table->char('Comparison')->comment('StringComparisonEnum - contains, equal');
            $table->string('Value');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        Schema::create('t_DocumentTaggingRules', static function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('TagId')->nullable()->constrained('t_DMSTags', 'Id');
            $table->foreignId('TaggingRuleId')->nullable()->constrained('t_DMSTaggingRules', 'Id');
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
            $table->text('Value');
            $table->char('DataType', 2);
            $table->foreignId('DocumentId')->constrained('t_Documents', 'Id');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        Schema::create('t_DocumentRelations', static function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('DocumentId')->constrained('t_Documents', 'Id');
            $table->string("Related");//string or related.
            $table->string("RelatedID", 100)->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');

            $table->index(["Related", "RelatedID"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_DocumentRelations');
        Schema::dropIfExists('t_DocumentAttributes');
        Schema::dropIfExists('t_DocumentTaggingRules');
        Schema::dropIfExists('t_DMSTaggingRules');
        Schema::dropIfExists('t_DocumentTags');
        Schema::dropIfExists('t_DMSTags');
        Schema::dropIfExists('t_DocumentVersions');
        Schema::dropIfExists('t_Documents');
        Schema::dropIfExists('t_Repositories');
    }
};
