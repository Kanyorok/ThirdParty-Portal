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
        Schema::create('t_LegalClauses', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('Title')->index('idx_clauses_title');
            $table->string('ClauseType', 100);
            $table->text('Content');
            $table->string('Version', 50)->index('idx_clauses_version');
            $table->string('IsStandard', 3)->default('No');
            $table->string('ClauseDMSDocID', 100)->nullable();
            $table->string('Status', 20)->default('ACTIVE');
            $table->date('EffectiveFrom')->nullable();
            $table->date('EffectiveTo')->nullable();
            $table->string('Jurisdiction', 120)->nullable();
            $table->text('Tags')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn')->useCurrent();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->index(['ClauseType', 'Status'], 'idx_clauses_type_status');
            $table->primary(['Id'], 'pk__t_legalc__3214ec07bf6288e3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_LegalClauses');
    }
};
