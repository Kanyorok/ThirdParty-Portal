<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_HRJobRequisitions', function (Blueprint $table) {
            $table->id('Id');
            $table->string('Code', 50)->unique();
            $table->string('Title', 150);
            $table->unsignedBigInteger('DepartmentID')->nullable();
            $table->unsignedBigInteger('BranchID')->nullable();
            $table->unsignedBigInteger('GradeID')->nullable();
            $table->unsignedBigInteger('RoleID')->nullable();
            $table->string('EmploymentType', 50)->nullable();
            $table->string('ContractType', 50)->nullable();
            $table->integer('Vacancies')->default(1);
            $table->string('Priority', 20)->nullable();
            $table->text('Justification')->nullable();
            $table->string('Status', 30)->default('Draft');
            $table->unsignedBigInteger('RequestedBy')->nullable();
            $table->dateTime('RequestedOn')->nullable();
            $table->unsignedBigInteger('ApprovedBy')->nullable();
            $table->dateTime('ApprovedOn')->nullable();
            $table->unsignedBigInteger('RejectedBy')->nullable();
            $table->dateTime('RejectedOn')->nullable();
            $table->string('RejectionReason', 255)->nullable();
            $table->dateTime('ClosedOn')->nullable();

            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->foreign('DepartmentID')->references('Id')->on('t_Departments');
            $table->foreign('BranchID')->references('Id')->on('t_Branches');
            $table->foreign('GradeID')->references('Id')->on('t_HRJobGrades');
            $table->foreign('RoleID')->references('Id')->on('t_HRJobRoles');
        });

        Schema::create('t_HRJobOpenings', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('RequisitionID')->nullable();
            $table->string('Code', 50)->unique();
            $table->string('Title', 150);
            $table->unsignedBigInteger('DepartmentID')->nullable();
            $table->unsignedBigInteger('BranchID')->nullable();
            $table->unsignedBigInteger('GradeID')->nullable();
            $table->unsignedBigInteger('RoleID')->nullable();
            $table->string('EmploymentType', 50)->nullable();
            $table->string('ContractType', 50)->nullable();
            $table->integer('Vacancies')->default(1);
            $table->text('Description')->nullable();
            $table->text('Requirements')->nullable();
            $table->string('Status', 30)->default('Draft');
            $table->dateTime('PublishedOn')->nullable();
            $table->date('CloseDate')->nullable();

            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->foreign('RequisitionID')->references('Id')->on('t_HRJobRequisitions');
            $table->foreign('DepartmentID')->references('Id')->on('t_Departments');
            $table->foreign('BranchID')->references('Id')->on('t_Branches');
            $table->foreign('GradeID')->references('Id')->on('t_HRJobGrades');
            $table->foreign('RoleID')->references('Id')->on('t_HRJobRoles');
        });

        Schema::create('t_HRApplicants', function (Blueprint $table) {
            $table->id('Id');
            $table->string('FirstName', 100);
            $table->string('LastName', 100);
            $table->string('OtherNames', 100)->nullable();
            $table->string('Email', 150)->nullable();
            $table->string('Phone', 50)->nullable();
            $table->string('Gender', 20)->nullable();
            $table->date('DateOfBirth')->nullable();
            $table->string('Address', 255)->nullable();
            $table->string('Source', 100)->nullable();
            $table->string('LinkedInUrl', 255)->nullable();
            $table->text('Notes')->nullable();

            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
        });

        Schema::create('t_HRJobApplications', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('JobOpeningID');
            $table->unsignedBigInteger('ApplicantID');
            $table->dateTime('AppliedOn')->useCurrent();
            $table->string('Status', 30)->default('New');
            $table->decimal('ExpectedSalary', 18, 2)->nullable();
            $table->integer('NoticePeriodDays')->nullable();
            $table->string('ResumePath', 255)->nullable();
            $table->string('CoverLetterPath', 255)->nullable();
            $table->string('Source', 100)->nullable();
            $table->text('Notes')->nullable();

            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->foreign('JobOpeningID')->references('Id')->on('t_HRJobOpenings');
            $table->foreign('ApplicantID')->references('Id')->on('t_HRApplicants');
        });

        Schema::create('t_HRJobApplicationDocuments', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('ApplicationID');
            $table->string('FileName', 255);
            $table->string('FilePath', 255);
            $table->string('Category', 100)->nullable();
            $table->string('Description', 255)->nullable();
            $table->unsignedBigInteger('UploadedBy')->nullable();
            $table->dateTime('UploadedOn')->useCurrent();

            $table->foreign('ApplicationID')->references('Id')->on('t_HRJobApplications')->onDelete('cascade');
        });

        Schema::create('t_HRJobApplicationScreenings', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('ApplicationID');
            $table->string('Status', 30)->default('Screened');
            $table->decimal('Score', 10, 2)->nullable();
            $table->text('Notes')->nullable();
            $table->unsignedBigInteger('ScreenedBy')->nullable();
            $table->dateTime('ScreenedOn')->nullable();

            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();

            $table->foreign('ApplicationID')->references('Id')->on('t_HRJobApplications')->onDelete('cascade');
        });

        Schema::create('t_HRJobInterviews', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('ApplicationID');
            $table->string('InterviewType', 100)->nullable();
            $table->dateTime('InterviewDate')->nullable();
            $table->string('Panel', 255)->nullable();
            $table->string('Location', 150)->nullable();
            $table->string('Status', 30)->default('Scheduled');
            $table->decimal('Score', 10, 2)->nullable();
            $table->text('Feedback')->nullable();
            $table->string('Recommendation', 30)->nullable();

            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();

            $table->foreign('ApplicationID')->references('Id')->on('t_HRJobApplications')->onDelete('cascade');
        });

        Schema::create('t_HRJobOffers', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('ApplicationID');
            $table->date('OfferDate')->nullable();
            $table->decimal('SalaryOffered', 18, 2)->nullable();
            $table->text('Benefits')->nullable();
            $table->string('Status', 30)->default('Draft');
            $table->dateTime('ApprovedOn')->nullable();
            $table->unsignedBigInteger('ApprovedBy')->nullable();
            $table->dateTime('SentOn')->nullable();
            $table->dateTime('AcceptedOn')->nullable();
            $table->dateTime('RejectedOn')->nullable();
            $table->text('Notes')->nullable();

            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();

            $table->foreign('ApplicationID')->references('Id')->on('t_HRJobApplications')->onDelete('cascade');
        });

        Schema::create('t_HROnboardingQueues', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('OfferID')->nullable();
            $table->unsignedBigInteger('ApplicationID')->nullable();
            $table->unsignedBigInteger('EmployeeID')->nullable();
            $table->string('CandidateName', 200)->nullable();
            $table->string('Status', 30)->default('Pending');
            $table->date('StartDate')->nullable();
            $table->text('Notes')->nullable();

            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();

            $table->foreign('OfferID')->references('Id')->on('t_HRJobOffers');
            $table->foreign('ApplicationID')->references('Id')->on('t_HRJobApplications');
            $table->foreign('EmployeeID')->references('Id')->on('t_HREmployees')->onDelete('set null');
        });

        Schema::create('t_HROnboardingTasks', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('OnboardingID');
            $table->string('Title', 150);
            $table->string('Description', 255)->nullable();
            $table->boolean('IsRequired')->default(1);
            $table->date('DueDate')->nullable();
            $table->string('Status', 30)->default('Pending');
            $table->unsignedBigInteger('CompletedBy')->nullable();
            $table->dateTime('CompletedOn')->nullable();

            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();

            $table->foreign('OnboardingID')->references('Id')->on('t_HROnboardingQueues')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HROnboardingTasks');
        Schema::dropIfExists('t_HROnboardingQueues');
        Schema::dropIfExists('t_HRJobOffers');
        Schema::dropIfExists('t_HRJobInterviews');
        Schema::dropIfExists('t_HRJobApplicationScreenings');
        Schema::dropIfExists('t_HRJobApplicationDocuments');
        Schema::dropIfExists('t_HRJobApplications');
        Schema::dropIfExists('t_HRApplicants');
        Schema::dropIfExists('t_HRJobOpenings');
        Schema::dropIfExists('t_HRJobRequisitions');
    }
};
