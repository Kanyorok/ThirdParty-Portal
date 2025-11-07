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
        Schema::table('t_legal_clause_template', function (Blueprint $table) {
            $table->foreign(['ClauseID'])->references(['Id'])->on('t_LegalClauses')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['TemplateID'])->references(['Id'])->on('t_LegalTemplates')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_legal_clause_template', function (Blueprint $table) {
            $table->dropForeign('t_legal_clause_template_clauseid_foreign');
            $table->dropForeign('t_legal_clause_template_createdby_foreign');
            $table->dropForeign('t_legal_clause_template_modifiedby_foreign');
            $table->dropForeign('t_legal_clause_template_templateid_foreign');
        });
    }
};
