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
        Schema::create('t_legal_clause_template', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('TemplateID')->constrained('t_LegalTemplates', 'Id');
            $table->foreignId('ClauseID')->constrained('t_LegalClauses', 'Id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_legal_clause_template');
    }
};
