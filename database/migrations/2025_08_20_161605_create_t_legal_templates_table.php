<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_LegalTemplates', function (Blueprint $table) {
            $table->id('Id');

            $table->string('Title', 255);
            $table->string('DocumentType', 100)->nullable();
            $table->string('Description', 500)->nullable();

            $table->longText('TemplateBody');
            $table->json('Tokens')->nullable();            // optional cache of placeholders/tokens
            $table->string('DocumentDMSID', 100)->nullable();

            $table->string('Version', 50)->default('v1.0');
            $table->string('Status', 20)->default('DRAFT'); // DRAFT|ACTIVE|DEPRECATED|ARCHIVED
            $table->boolean('IsActive')->default(true);

            // Effective dating / jurisdiction
            $table->date('EffectiveFrom')->nullable();
            $table->date('EffectiveTo')->nullable();
            $table->string('Jurisdiction', 120)->nullable();

            // Approvals (maker-checker)
            $table->string('ApprovalStatus', 20)->default('PENDING'); // PENDING|APPROVED|REJECTED
            $table->text('ApprovalReason')->nullable();
            $table->foreignId('ApprovedBy')->nullable()->constrained('t_Users','Id');
            $table->dateTime('ApprovedOn')->nullable();

            // Audit
            $table->foreignId('CreatedBy')->constrained('t_Users','Id');
            $table->dateTime('CreatedOn')->useCurrent();
            $table->foreignId('ModifiedBy')->nullable()->constrained('t_Users','Id');
            $table->dateTime('ModifiedOn')->nullable();
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users','Id');
            $table->softDeletes('DeletedOn');

            // Constraints & indexes
            $table->unique(['Title','Version','DocumentType'], 'uq_Templates_Title_Version_Type');
            $table->index(['DocumentType','Status'], 'idx_Templates_DocType_Status');
            $table->index('Title', 'idx_Templates_Title');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_LegalTemplates');
    }
};
