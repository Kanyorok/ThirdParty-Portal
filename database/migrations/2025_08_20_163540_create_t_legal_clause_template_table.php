<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_legal_clause_template', function (Blueprint $table) {
            $table->id('Id');

            $table->foreignId('TemplateID')
                ->constrained('t_LegalTemplates', 'Id')
                ->cascadeOnDelete();

            $table->foreignId('ClauseID')
                ->constrained('t_LegalClauses', 'Id')
                ->noActionOnDelete(); // SQL Server-safe replacement for "restrict"

            // Order & metadata within the template
            $table->unsignedSmallInteger('Position')->default(1);
            $table->boolean('IsMandatory')->default(false);
            $table->string('ClauseVersion', 50)->nullable();

            // Optional per-template overrides (do NOT change master)
            $table->string('TitleOverride', 255)->nullable();
            $table->longText('ContentOverride')->nullable();

            // Audit
            $table->foreignId('CreatedBy')->nullable()->constrained('t_Users','Id');
            $table->dateTime('CreatedOn')->useCurrent();
            $table->foreignId('ModifiedBy')->nullable()->constrained('t_Users','Id');
            $table->dateTime('ModifiedOn')->nullable();

            // Constraints & indexes
            $table->unique(['TemplateID','ClauseID'], 'uq_Template_Clause');
            $table->index(['TemplateID','Position'], 'idx_Template_Position');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_legal_clause_template');
    }
};
