<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_CRMTrainingCertificateTemplates', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('ProgramID')->nullable();
            $table->string('Name', 150);
            $table->text('Description')->nullable();
            $table->string('DefaultIssuingBody', 150)->nullable();
            $table->integer('DefaultValidityMonths')->nullable();
            $table->unsignedBigInteger('TemplateDocumentId')->nullable();
            $table->boolean('IsSample')->default(0);
            $table->boolean('IsActive')->default(1);
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->foreign('ProgramID', 'fk_crm_cert_tpl_program')
                ->references('Id')
                ->on('t_CRMTrainingPrograms')
                ->onDelete('set null');
            $table->foreign('TemplateDocumentId', 'fk_crm_cert_tpl_document')
                ->references('Id')
                ->on('t_Documents')
                ->onDelete('set null');
        });

        DB::table('t_CRMTrainingCertificateTemplates')->insert([
            [
                'ProgramID' => null,
                'Name' => 'Standard Completion Certificate',
                'Description' => 'General certificate template for participants who successfully complete training.',
                'DefaultIssuingBody' => 'BR_ERP Academy',
                'DefaultValidityMonths' => null,
                'TemplateDocumentId' => null,
                'IsSample' => 1,
                'IsActive' => 1,
                'CreatedBy' => null,
                'CreatedOn' => now(),
                'ModifiedBy' => null,
                'ModifiedOn' => null,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ],
            [
                'ProgramID' => null,
                'Name' => 'Participation Certificate',
                'Description' => 'Participation certificate template with default 24-month validity.',
                'DefaultIssuingBody' => 'BR_ERP Academy',
                'DefaultValidityMonths' => 24,
                'TemplateDocumentId' => null,
                'IsSample' => 1,
                'IsActive' => 1,
                'CreatedBy' => null,
                'CreatedOn' => now(),
                'ModifiedBy' => null,
                'ModifiedOn' => null,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ],
        ]);

        Schema::table('t_CRMTrainingCertificates', function (Blueprint $table) {
            $table->unsignedBigInteger('TemplateID')->nullable();
            $table->foreign('TemplateID', 'fk_crm_cert_template')
                ->references('Id')
                ->on('t_CRMTrainingCertificateTemplates')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('t_CRMTrainingCertificates', function (Blueprint $table) {
            $table->dropForeign('fk_crm_cert_template');
            $table->dropColumn('TemplateID');
        });

        Schema::table('t_CRMTrainingCertificateTemplates', function (Blueprint $table) {
            $table->dropForeign('fk_crm_cert_tpl_program');
            $table->dropForeign('fk_crm_cert_tpl_document');
        });

        Schema::dropIfExists('t_CRMTrainingCertificateTemplates');
    }
};
