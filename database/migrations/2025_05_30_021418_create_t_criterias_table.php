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
        if (Schema::hasTable('t_Criterias')) {
            return;
        }
        Schema::create('t_Criterias', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('SectionID')
                ->constrained('t_Sections', 'Id')
                ->onDelete('cascade');

            $table->string('CriteriaName', 100);
            $table->text('Description')->nullable();
            $table->boolean('IsActive')->default(true);

            $table->foreignId('CreatedBy')->nullable()->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn')->useCurrent();
            $table->foreignId('ModifiedBy')->nullable()->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn')->useCurrent()->useCurrentOnUpdate();
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');

            $table->unique(['SectionID', 'CriteriaName']);
            $table->index('IsActive');
            $table->index('DeletedOn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Criterias');
    }
};
