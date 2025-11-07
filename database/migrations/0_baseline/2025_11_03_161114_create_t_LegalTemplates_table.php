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
        Schema::create('t_LegalTemplates', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('Title')->index('idx_templates_title');
            $table->string('DocumentType', 100)->nullable();
            $table->string('Description', 500)->nullable();
            $table->text('TemplateBody');
            $table->text('Tokens')->nullable();
            $table->string('DocumentDMSID', 100)->nullable();
            $table->string('Version', 50)->default('v1.0');
            $table->string('Status', 20)->default('DRAFT');
            $table->boolean('IsActive')->default(true);
            $table->date('EffectiveFrom')->nullable();
            $table->date('EffectiveTo')->nullable();
            $table->string('Jurisdiction', 120)->nullable();
            $table->string('ApprovalStatus', 20)->default('PENDING');
            $table->text('ApprovalReason')->nullable();
            $table->bigInteger('ApprovedBy')->nullable();
            $table->dateTime('ApprovedOn')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn')->useCurrent();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->index(['DocumentType', 'Status'], 'idx_templates_doctype_status');
            $table->primary(['Id'], 'pk__t_legalt__3214ec0765078bd9');
            $table->unique(['Title', 'Version', 'DocumentType'], 'uq_templates_title_version_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_LegalTemplates');
    }
};
