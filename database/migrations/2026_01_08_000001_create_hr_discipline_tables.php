<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_HRDisciplinaryLegalRefs', function (Blueprint $table) {
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

        Schema::create('t_HRDisciplinaryPolicies', function (Blueprint $table) {
            $table->id('Id');
            $table->string('Name', 150);
            $table->date('EffectiveFrom')->nullable();
            $table->date('EffectiveTo')->nullable();
            $table->text('EmploymentTypes')->nullable();
            $table->text('ContractTypes')->nullable();
            $table->text('ProgressiveRules')->nullable();
            $table->integer('AppealDeadlineDays')->default(7);
            $table->integer('RetentionMonths')->default(24);
            $table->boolean('AllowDirectHearing')->default(0);
            $table->boolean('IsActive')->default(1);
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
        });

        Schema::create('t_HRDisciplinarySanctions', function (Blueprint $table) {
            $table->id('Id');
            $table->string('Code', 30)->unique();
            $table->string('Name', 150);
            $table->text('Description')->nullable();
            $table->boolean('IsSuspension')->default(0);
            $table->boolean('SuspensionWithoutPay')->default(0);
            $table->boolean('AffectsPayroll')->default(0);
            $table->boolean('BlocksLeave')->default(0);
            $table->boolean('UpdatesEmploymentStatus')->default(0);
            $table->string('EmploymentStatus', 50)->nullable();
            $table->integer('DefaultDurationDays')->nullable();
            $table->boolean('IsActive')->default(1);
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
        });

        Schema::create('t_HRDisciplinaryOffenceCategories', function (Blueprint $table) {
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

        Schema::create('t_HRDisciplinaryOffences', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('CategoryID')->nullable();
            $table->string('Code', 50)->unique();
            $table->string('Name', 150);
            $table->string('Severity', 30)->default('Minor');
            $table->unsignedBigInteger('RecommendedSanctionID')->nullable();
            $table->boolean('HearingRequired')->default(0);
            $table->boolean('SummaryDismissalAllowed')->default(0);
            $table->boolean('RequiresEvidence')->default(0);
            $table->boolean('RequiresApproval')->default(0);
            $table->boolean('IsActive')->default(1);
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->foreign('CategoryID')->references('Id')->on('t_HRDisciplinaryOffenceCategories');
            $table->foreign('RecommendedSanctionID')->references('Id')->on('t_HRDisciplinarySanctions');
        });

        Schema::create('t_HRDisciplinaryLetterTemplates', function (Blueprint $table) {
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

        Schema::create('t_HRDisciplinaryCases', function (Blueprint $table) {
            $table->id('Id');
            $table->string('CaseNo', 30)->unique();
            $table->unsignedBigInteger('EmployeeID');
            $table->string('ComplainantType', 30)->nullable();
            $table->unsignedBigInteger('ComplainantID')->nullable();
            $table->string('ComplainantName', 150)->nullable();
            $table->unsignedBigInteger('OffenceID')->nullable();
            $table->unsignedBigInteger('PolicyID')->nullable();
            $table->string('Severity', 30)->nullable();
            $table->date('IncidentDate')->nullable();
            $table->date('ReportedDate')->nullable();
            $table->text('Description')->nullable();
            $table->string('Status', 40)->default('Reported');
            $table->boolean('HearingRequired')->default(0);
            $table->boolean('SummaryDismissalAllowed')->default(0);
            $table->boolean('EvidenceRequired')->default(0);
            $table->dateTime('ClosedOn')->nullable();
            $table->unsignedBigInteger('ClosedBy')->nullable();
            $table->string('Outcome', 100)->nullable();
            $table->date('RetentionUntil')->nullable();

            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->foreign('EmployeeID')->references('Id')->on('t_HREmployees');
            $table->foreign('ComplainantID')->references('Id')->on('t_HREmployees');
            $table->foreign('OffenceID')->references('Id')->on('t_HRDisciplinaryOffences');
            $table->foreign('PolicyID')->references('Id')->on('t_HRDisciplinaryPolicies');
        });

        Schema::create('t_HRDisciplinaryCaseStatusLogs', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('CaseID');
            $table->string('FromStatus', 40)->nullable();
            $table->string('ToStatus', 40);
            $table->text('Remarks')->nullable();
            $table->unsignedBigInteger('ChangedBy')->nullable();
            $table->dateTime('ChangedOn')->useCurrent();

            $table->foreign('CaseID')->references('Id')->on('t_HRDisciplinaryCases');
        });

        Schema::create('t_HRDisciplinaryCaseDocuments', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('CaseID');
            $table->unsignedBigInteger('DocumentId')->nullable();
            $table->string('DocType', 50)->nullable();
            $table->string('Title', 150)->nullable();
            $table->text('Notes')->nullable();
            $table->unsignedBigInteger('UploadedBy')->nullable();
            $table->dateTime('UploadedOn')->useCurrent();

            $table->foreign('CaseID')->references('Id')->on('t_HRDisciplinaryCases');
            $table->foreign('DocumentId')->references('Id')->on('t_Documents');
        });

        Schema::create('t_HRDisciplinaryInvestigations', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('CaseID');
            $table->string('InvestigatorType', 30)->default('Internal');
            $table->unsignedBigInteger('InvestigatorID')->nullable();
            $table->string('InvestigatorName', 150)->nullable();
            $table->date('StartDate')->nullable();
            $table->date('EndDate')->nullable();
            $table->boolean('ConflictDeclared')->default(0);
            $table->text('FindingsSummary')->nullable();
            $table->string('Status', 30)->default('Draft');
            $table->unsignedBigInteger('ApprovedBy')->nullable();
            $table->dateTime('ApprovedOn')->nullable();
            $table->unsignedBigInteger('ReportDocumentId')->nullable();

            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();

            $table->foreign('CaseID')->references('Id')->on('t_HRDisciplinaryCases');
            $table->foreign('InvestigatorID')->references('Id')->on('t_HREmployees');
            $table->foreign('ReportDocumentId')->references('Id')->on('t_Documents');
        });

        Schema::create('t_HRDisciplinaryInvestigationDocuments', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('InvestigationID');
            $table->unsignedBigInteger('DocumentId')->nullable();
            $table->string('DocType', 50)->nullable();
            $table->text('Notes')->nullable();
            $table->unsignedBigInteger('UploadedBy')->nullable();
            $table->dateTime('UploadedOn')->useCurrent();

            $table->foreign('InvestigationID')->references('Id')->on('t_HRDisciplinaryInvestigations');
            $table->foreign('DocumentId')->references('Id')->on('t_Documents');
        });

        Schema::create('t_HRDisciplinaryNotices', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('CaseID');
            $table->string('NoticeType', 50)->default('ShowCause');
            $table->unsignedBigInteger('TemplateID')->nullable();
            $table->date('IssuedOn')->nullable();
            $table->date('ResponseDueOn')->nullable();
            $table->string('Status', 30)->default('Issued');
            $table->text('Summary')->nullable();
            $table->unsignedBigInteger('AcknowledgedBy')->nullable();
            $table->dateTime('AcknowledgedOn')->nullable();

            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();

            $table->foreign('CaseID')->references('Id')->on('t_HRDisciplinaryCases');
            $table->foreign('TemplateID')->references('Id')->on('t_LegalTemplates');
            $table->foreign('AcknowledgedBy')->references('Id')->on('t_HREmployees');
        });

        Schema::create('t_HRDisciplinaryResponses', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('CaseID');
            $table->text('ResponseText')->nullable();
            $table->unsignedBigInteger('SubmittedBy')->nullable();
            $table->dateTime('SubmittedOn')->nullable();
            $table->string('Status', 30)->default('Submitted');
            $table->unsignedBigInteger('ResponseDocumentId')->nullable();

            $table->foreign('CaseID')->references('Id')->on('t_HRDisciplinaryCases');
            $table->foreign('SubmittedBy')->references('Id')->on('t_HREmployees');
            $table->foreign('ResponseDocumentId')->references('Id')->on('t_Documents');
        });

        Schema::create('t_HRDisciplinaryHearings', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('CaseID');
            $table->dateTime('HearingDate')->nullable();
            $table->string('Venue', 150)->nullable();
            $table->unsignedBigInteger('HRFacilitatorID')->nullable();
            $table->string('EmployeeRepName', 150)->nullable();
            $table->string('Status', 30)->default('Scheduled');
            $table->unsignedBigInteger('MinutesDocumentId')->nullable();
            $table->text('PanelRecommendation')->nullable();

            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();

            $table->foreign('CaseID')->references('Id')->on('t_HRDisciplinaryCases');
            $table->foreign('HRFacilitatorID')->references('Id')->on('t_HREmployees');
            $table->foreign('MinutesDocumentId')->references('Id')->on('t_Documents');
        });

        Schema::create('t_HRDisciplinaryHearingPanel', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('HearingID');
            $table->unsignedBigInteger('EmployeeID');
            $table->string('Role', 100)->nullable();

            $table->foreign('HearingID')->references('Id')->on('t_HRDisciplinaryHearings');
            $table->foreign('EmployeeID')->references('Id')->on('t_HREmployees');
        });

        Schema::create('t_HRDisciplinaryHearingAttendance', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('HearingID');
            $table->string('AttendeeName', 150)->nullable();
            $table->string('AttendeeRole', 100)->nullable();
            $table->boolean('Present')->default(1);
            $table->text('Notes')->nullable();

            $table->foreign('HearingID')->references('Id')->on('t_HRDisciplinaryHearings');
        });

        Schema::create('t_HRDisciplinaryDecisions', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('CaseID');
            $table->date('DecisionDate')->nullable();
            $table->text('DecisionSummary')->nullable();
            $table->string('PolicyClause', 150)->nullable();
            $table->unsignedBigInteger('LegalRefID')->nullable();
            $table->unsignedBigInteger('InvestigationID')->nullable();
            $table->unsignedBigInteger('HearingID')->nullable();
            $table->unsignedBigInteger('SanctionID')->nullable();
            $table->date('SanctionStartDate')->nullable();
            $table->date('SanctionEndDate')->nullable();
            $table->boolean('PayrollImpact')->default(0);
            $table->string('Status', 30)->default('Pending');
            $table->unsignedBigInteger('ApprovedBy')->nullable();
            $table->dateTime('ApprovedOn')->nullable();

            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();

            $table->foreign('CaseID')->references('Id')->on('t_HRDisciplinaryCases');
            $table->foreign('LegalRefID')->references('Id')->on('t_HRDisciplinaryLegalRefs');
            $table->foreign('InvestigationID')->references('Id')->on('t_HRDisciplinaryInvestigations');
            $table->foreign('HearingID')->references('Id')->on('t_HRDisciplinaryHearings');
            $table->foreign('SanctionID')->references('Id')->on('t_HRDisciplinarySanctions');
        });

        Schema::create('t_HRDisciplinaryAppeals', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('CaseID');
            $table->date('AppealDate')->nullable();
            $table->text('Grounds')->nullable();
            $table->date('DeadlineDate')->nullable();
            $table->string('Status', 30)->default('Submitted');
            $table->string('Outcome', 30)->nullable();
            $table->text('DecisionSummary')->nullable();
            $table->dateTime('HearingDate')->nullable();
            $table->unsignedBigInteger('ApprovedBy')->nullable();
            $table->dateTime('ApprovedOn')->nullable();

            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();

            $table->foreign('CaseID')->references('Id')->on('t_HRDisciplinaryCases');
        });

        Schema::create('t_HRDisciplinaryAppealPanel', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('AppealID');
            $table->unsignedBigInteger('EmployeeID');
            $table->string('Role', 100)->nullable();

            $table->foreign('AppealID')->references('Id')->on('t_HRDisciplinaryAppeals');
            $table->foreign('EmployeeID')->references('Id')->on('t_HREmployees');
        });

        Schema::create('t_HRDisciplinaryAppealDocuments', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('AppealID');
            $table->unsignedBigInteger('DocumentId')->nullable();
            $table->string('DocType', 50)->nullable();
            $table->text('Notes')->nullable();
            $table->unsignedBigInteger('UploadedBy')->nullable();
            $table->dateTime('UploadedOn')->useCurrent();

            $table->foreign('AppealID')->references('Id')->on('t_HRDisciplinaryAppeals');
            $table->foreign('DocumentId')->references('Id')->on('t_Documents');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HRDisciplinaryAppealDocuments');
        Schema::dropIfExists('t_HRDisciplinaryAppealPanel');
        Schema::dropIfExists('t_HRDisciplinaryAppeals');
        Schema::dropIfExists('t_HRDisciplinaryDecisions');
        Schema::dropIfExists('t_HRDisciplinaryHearingAttendance');
        Schema::dropIfExists('t_HRDisciplinaryHearingPanel');
        Schema::dropIfExists('t_HRDisciplinaryHearings');
        Schema::dropIfExists('t_HRDisciplinaryResponses');
        Schema::dropIfExists('t_HRDisciplinaryNotices');
        Schema::dropIfExists('t_HRDisciplinaryInvestigationDocuments');
        Schema::dropIfExists('t_HRDisciplinaryInvestigations');
        Schema::dropIfExists('t_HRDisciplinaryCaseDocuments');
        Schema::dropIfExists('t_HRDisciplinaryCaseStatusLogs');
        Schema::dropIfExists('t_HRDisciplinaryCases');
        Schema::dropIfExists('t_HRDisciplinaryLetterTemplates');
        Schema::dropIfExists('t_HRDisciplinaryOffences');
        Schema::dropIfExists('t_HRDisciplinaryOffenceCategories');
        Schema::dropIfExists('t_HRDisciplinarySanctions');
        Schema::dropIfExists('t_HRDisciplinaryPolicies');
        Schema::dropIfExists('t_HRDisciplinaryLegalRefs');
    }
};
