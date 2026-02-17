<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_HRExitLegalRefs', function (Blueprint $table) {
            $table->id('Id');
            $table->string('Code', 20)->unique();
            $table->string('Section', 50);
            $table->string('Title', 150);
            $table->text('Description')->nullable();
            $table->boolean('IsActive')->default(1);
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
        });

        Schema::create('t_HRExitTypes', function (Blueprint $table) {
            $table->id('Id');
            $table->string('Code', 30)->unique();
            $table->string('Name', 150);
            $table->text('Description')->nullable();
            $table->boolean('IsEmployerInitiated')->default(0);
            $table->boolean('RequiresCase')->default(0);
            $table->boolean('RequiresHearing')->default(0);
            $table->boolean('IsRedundancy')->default(0);
            $table->boolean('IsSummaryDismissal')->default(0);
            $table->boolean('IsActive')->default(1);
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
        });


        Schema::create('t_HRExitClearanceDepartments', function (Blueprint $table) {
            $table->id('Id');
            $table->string('Name', 150);
            $table->integer('Sequence')->default(1);
            $table->boolean('IsActive')->default(1);
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
        });

        Schema::create('t_HRExitChecklistTemplates', function (Blueprint $table) {
            $table->id('Id');
            $table->string('Name', 150);
            $table->text('Description')->nullable();
            $table->boolean('IsActive')->default(1);
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
        });

        Schema::create('t_HRExitPolicies', function (Blueprint $table) {
            $table->id('Id');
            $table->string('Name', 150);
            $table->date('EffectiveFrom')->nullable();
            $table->date('EffectiveTo')->nullable();
            $table->text('EmploymentTypes')->nullable();
            $table->text('ContractTypes')->nullable();
            $table->text('ApprovalWorkflow')->nullable();
            $table->text('RedundancyCriteria')->nullable();
            $table->text('TerminalDuesConfig')->nullable();
            $table->unsignedBigInteger('ChecklistTemplateID')->nullable();
            $table->boolean('AllowNoticePay')->default(1);
            $table->boolean('AllowNoticeWaiver')->default(1);
            $table->boolean('IsActive')->default(1);
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->foreign('ChecklistTemplateID')->references('Id')->on('t_HRExitChecklistTemplates');
        });

        Schema::create('t_HRExitPolicyNoticePeriods', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('PolicyID');
            $table->string('EmploymentType', 50)->nullable();
            $table->string('ContractType', 50)->nullable();
            $table->integer('NoticeDays')->default(0);
            $table->boolean('PayInLieuAllowed')->default(1);
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->foreign('PolicyID')->references('Id')->on('t_HRExitPolicies');
        });

        Schema::create('t_HRExitChecklistItems', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('TemplateID');
            $table->unsignedBigInteger('ClearanceDepartmentID')->nullable();
            $table->string('ItemName', 150);
            $table->integer('Sequence')->default(1);
            $table->boolean('IsMandatory')->default(1);
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->foreign('TemplateID')->references('Id')->on('t_HRExitChecklistTemplates');
            $table->foreign('ClearanceDepartmentID')->references('Id')->on('t_HRExitClearanceDepartments');
        });

        Schema::create('t_HRExitRedundancies', function (Blueprint $table) {
            $table->id('Id');
            $table->string('RefNo', 30)->unique();
            $table->string('Reason', 150)->nullable();
            $table->text('Criteria')->nullable();
            $table->string('SelectionMethod', 100)->nullable();
            $table->boolean('UnionNotified')->default(0);
            $table->date('UnionNotifiedOn')->nullable();
            $table->boolean('LabourOfficeNotified')->default(0);
            $table->date('LabourOfficeNotifiedOn')->nullable();
            $table->text('Notes')->nullable();
            $table->string('Status', 30)->default('Draft');
            $table->unsignedBigInteger('ApprovedBy')->nullable();
            $table->dateTime('ApprovedOn')->nullable();
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
        });

        Schema::create('t_HRExitRequests', function (Blueprint $table) {
            $table->id('Id');
            $table->string('ExitNo', 30)->unique();
            $table->unsignedBigInteger('EmployeeID');
            $table->unsignedBigInteger('ExitTypeID');
            $table->unsignedBigInteger('PolicyID')->nullable();
            $table->unsignedBigInteger('CaseID')->nullable();
            $table->unsignedBigInteger('RedundancyID')->nullable();
            $table->string('InitiatorType', 30)->default('Employee');
            $table->unsignedBigInteger('InitiatedBy')->nullable();
            $table->dateTime('InitiatedOn')->nullable();
            $table->date('RequestedOn')->nullable();
            $table->text('Reason')->nullable();
            $table->date('NoticeDate')->nullable();
            $table->date('ProposedLastDay')->nullable();
            $table->date('EffectiveExitDate')->nullable();
            $table->integer('NoticeDays')->default(0);
            $table->boolean('NoticePayInLieu')->default(0);
            $table->decimal('NoticePayAmount', 18, 2)->nullable();
            $table->boolean('NoticeWaived')->default(0);
            $table->string('ApprovalStatus', 30)->default('Pending');
            $table->unsignedBigInteger('ApprovedBy')->nullable();
            $table->dateTime('ApprovedOn')->nullable();
            $table->unsignedBigInteger('FinalPayrollRunID')->nullable();
            $table->string('FinalPayrollStatus', 30)->nullable();
            $table->dateTime('FinalPayrollOn')->nullable();
            $table->string('Status', 30)->default('Draft');
            $table->text('Remarks')->nullable();
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->foreign('EmployeeID')->references('Id')->on('t_HREmployees');
            $table->foreign('ExitTypeID')->references('Id')->on('t_HRExitTypes');
            $table->foreign('PolicyID')->references('Id')->on('t_HRExitPolicies');
            $table->foreign('CaseID')->references('Id')->on('t_HRDisciplinaryCases');
            $table->foreign('RedundancyID')->references('Id')->on('t_HRExitRedundancies');
            $table->foreign('FinalPayrollRunID')->references('Id')->on('t_HRPayrollRuns');
        });

        Schema::create('t_HRExitStatusLogs', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('ExitID');
            $table->string('FromStatus', 30)->nullable();
            $table->string('ToStatus', 30);
            $table->string('Remarks', 255)->nullable();
            $table->unsignedBigInteger('ChangedBy')->nullable();
            $table->dateTime('ChangedOn')->useCurrent();

            $table->foreign('ExitID')->references('Id')->on('t_HRExitRequests');
        });

        Schema::create('t_HRExitNotices', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('ExitID');
            $table->string('NoticeType', 50);
            $table->unsignedBigInteger('TemplateID')->nullable();
            $table->string('DeliveryMethod', 50)->nullable();
            $table->unsignedBigInteger('DocumentId')->nullable();
            $table->date('IssuedOn')->nullable();
            $table->date('ResponseDue')->nullable();
            $table->string('Status', 30)->default('Draft');
            $table->string('DeliveryStatus', 30)->nullable();
            $table->unsignedBigInteger('SentBy')->nullable();
            $table->dateTime('SentOn')->nullable();
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->foreign('ExitID')->references('Id')->on('t_HRExitRequests');
            $table->foreign('TemplateID')->references('Id')->on('t_LegalTemplates');
            $table->foreign('DocumentId')->references('Id')->on('t_Documents');
        });

        Schema::create('t_HRExitClearances', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('ExitID');
            $table->unsignedBigInteger('ClearanceDepartmentID');
            $table->string('Status', 30)->default('Pending');
            $table->unsignedBigInteger('ClearedBy')->nullable();
            $table->dateTime('ClearedOn')->nullable();
            $table->string('Remarks', 255)->nullable();
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->foreign('ExitID')->references('Id')->on('t_HRExitRequests');
            $table->foreign('ClearanceDepartmentID')->references('Id')->on('t_HRExitClearanceDepartments');
        });

        Schema::create('t_HRExitTerminalDues', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('ExitID');
            $table->string('ComponentCode', 50)->nullable();
            $table->string('ComponentName', 150);
            $table->boolean('IsEarning')->default(1);
            $table->boolean('IsTaxable')->default(0);
            $table->decimal('Amount', 18, 2)->default(0);
            $table->text('Notes')->nullable();
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->foreign('ExitID')->references('Id')->on('t_HRExitRequests');
        });

        Schema::create('t_HRExitDocuments', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('ExitID');
            $table->unsignedBigInteger('DocumentId')->nullable();
            $table->string('DocType', 50)->nullable();
            $table->unsignedBigInteger('UploadedBy')->nullable();
            $table->dateTime('UploadedOn')->useCurrent();

            $table->foreign('ExitID')->references('Id')->on('t_HRExitRequests');
            $table->foreign('DocumentId')->references('Id')->on('t_Documents');
        });

        Schema::create('t_HRExitInterviews', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('ExitID');
            $table->unsignedBigInteger('InterviewerID')->nullable();
            $table->date('InterviewDate')->nullable();
            $table->string('Mode', 50)->nullable();
            $table->string('AttritionReason', 150)->nullable();
            $table->text('Notes')->nullable();
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->foreign('ExitID')->references('Id')->on('t_HRExitRequests');
            $table->foreign('InterviewerID')->references('Id')->on('t_HREmployees');
        });

        Schema::create('t_HRExitLetterTemplates', function (Blueprint $table) {
            $table->id('Id');
            $table->string('LetterType', 50);
            $table->unsignedBigInteger('TemplateID')->nullable();
            $table->boolean('IsActive')->default(1);
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->foreign('TemplateID')->references('Id')->on('t_LegalTemplates');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HRExitLetterTemplates');
        Schema::dropIfExists('t_HRExitInterviews');
        Schema::dropIfExists('t_HRExitDocuments');
        Schema::dropIfExists('t_HRExitTerminalDues');
        Schema::dropIfExists('t_HRExitClearances');
        Schema::dropIfExists('t_HRExitNotices');
        Schema::dropIfExists('t_HRExitStatusLogs');
        Schema::dropIfExists('t_HRExitRequests');
        Schema::dropIfExists('t_HRExitRedundancies');
        Schema::dropIfExists('t_HRExitChecklistItems');
        Schema::dropIfExists('t_HRExitChecklistTemplates');
        Schema::dropIfExists('t_HRExitClearanceDepartments');
        Schema::dropIfExists('t_HRExitPolicyNoticePeriods');
        Schema::dropIfExists('t_HRExitPolicies');
        Schema::dropIfExists('t_HRExitTypes');
        Schema::dropIfExists('t_HRExitLegalRefs');
    }
};
