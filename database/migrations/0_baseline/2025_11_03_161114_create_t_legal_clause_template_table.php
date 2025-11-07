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
            $table->bigIncrements('Id');
            $table->bigInteger('TemplateID');
            $table->bigInteger('ClauseID');
            $table->smallInteger('Position')->default(1);
            $table->boolean('IsMandatory')->default(false);
            $table->string('ClauseVersion', 50)->nullable();
            $table->string('TitleOverride')->nullable();
            $table->text('ContentOverride')->nullable();
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();

            $table->index(['TemplateID', 'Position'], 'idx_template_position');
            $table->unique(['TemplateID', 'ClauseID'], 'uq_template_clause');
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
