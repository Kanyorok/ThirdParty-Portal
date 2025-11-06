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
        Schema::create('t_SysFilters', static function (Blueprint $table) {
            $table->id('Id');
            $table->string('Name');
            $table->string('FieldName');
            $table->string('Relation')->nullable();//if null field else related.
            $table->string('RelationSource')->nullable();
            $table->char('DataType', 2);
            $table->char('Operator', 2);
            $table->string('Source')->index();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        Schema::create('t_MarketingListsFilters', static function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('MarketingListId')->constrained('t_MarketingLists', 'MarketingListID')->cascadeOnUpdate()->cascadeOnUpdate();
            $table->foreignId('FilterId')->constrained('t_SysFilters', 'Id');//multiple use values, single use value
            $table->string('FilterValue')->nullable();
            $table->jsonb('FilterValues')->nullable();
            $table->enum('After', ['and', 'or'])->default('and');
            $table->integer('DisplayOrder');
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
        Schema::dropIfExists('t_MarketingListsFilters');
        Schema::dropIfExists('t_SysFilters');
    }
};
