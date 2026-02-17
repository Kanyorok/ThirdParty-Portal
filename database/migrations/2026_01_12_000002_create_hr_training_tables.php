<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_HRTrainingCategories', function (Blueprint $table) {
            $table->id('Id');
            $table->string('Code', 30)->nullable()->unique();
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

        Schema::create('t_HRTrainingTrainers', function (Blueprint $table) {
            $table->id('Id');
            $table->string('TrainerType', 30)->default('Internal');
            $table->unsignedBigInteger('EmployeeID')->nullable();
            $table->string('Name', 150)->nullable();
            $table->string('Email', 150)->nullable();
            $table->string('Phone', 50)->nullable();
            $table->text('Expertise')->nullable();
            $table->text('Certifications')->nullable();
            $table->decimal('Rate', 18, 2)->nullable();
            $table->boolean('IsActive')->default(1);
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->foreign('EmployeeID')->references('Id')->on('t_HREmployees');
        });

        Schema::create('t_HRTrainingPrograms', function (Blueprint $table) {
            $table->id('Id');
            $table->string('Code', 30)->unique();
            $table->string('Title', 200);
            $table->unsignedBigInteger('CategoryID')->nullable();
            $table->string('DeliveryMode', 50)->nullable();
            $table->decimal('DurationHours', 10, 2)->nullable();
            $table->text('Objectives')->nullable();
            $table->text('TargetAudience')->nullable();
            $table->decimal('BudgetedCost', 18, 2)->nullable();
            $table->decimal('ActualCost', 18, 2)->nullable();
            $table->boolean('IsMandatory')->default(0);
            $table->boolean('HasCertification')->default(0);
            $table->string('Status', 30)->default('Active');
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->foreign('CategoryID')->references('Id')->on('t_HRTrainingCategories');
        });

        Schema::create('t_HRTrainingProgramTargets', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('ProgramID');
            $table->string('TargetType', 30);
            $table->unsignedBigInteger('TargetID');
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->index(['TargetType', 'TargetID'], 'ix_hr_training_targets_type_id');
            $table->unique(['ProgramID', 'TargetType', 'TargetID'], 'ux_hr_training_targets');
            $table->foreign('ProgramID')->references('Id')->on('t_HRTrainingPrograms')->onDelete('cascade');
        });

        Schema::create('t_HRTrainingSessions', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('ProgramID');
            $table->string('SessionCode', 30)->nullable();
            $table->string('Title', 200)->nullable();
            $table->date('StartDate')->nullable();
            $table->date('EndDate')->nullable();
            $table->time('StartTime')->nullable();
            $table->time('EndTime')->nullable();
            $table->string('Location', 150)->nullable();
            $table->string('OnlineLink', 255)->nullable();
            $table->unsignedBigInteger('TrainerID')->nullable();
            $table->integer('MaxParticipants')->nullable();
            $table->string('Status', 30)->default('Planned');
            $table->unsignedBigInteger('AgendaDocumentId')->nullable();
            $table->unsignedBigInteger('MaterialsDocumentId')->nullable();
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->foreign('ProgramID')->references('Id')->on('t_HRTrainingPrograms')->onDelete('cascade');
            $table->foreign('TrainerID')->references('Id')->on('t_HRTrainingTrainers');
            $table->foreign('AgendaDocumentId')->references('Id')->on('t_Documents');
            $table->foreign('MaterialsDocumentId')->references('Id')->on('t_Documents');
        });

        Schema::create('t_HRTrainingSessionParticipants', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('SessionID');
            $table->unsignedBigInteger('EmployeeID');
            $table->string('EnrollmentMethod', 30)->default('HR');
            $table->string('Status', 30)->default('Nominated');
            $table->string('AttendanceStatus', 30)->nullable();
            $table->dateTime('AttendanceMarkedOn')->nullable();
            $table->unsignedBigInteger('AttendanceMarkedBy')->nullable();
            $table->dateTime('EnrolledOn')->nullable();
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->unique(['SessionID', 'EmployeeID'], 'ux_hr_training_session_participant');
            $table->foreign('SessionID')->references('Id')->on('t_HRTrainingSessions')->onDelete('cascade');
            $table->foreign('EmployeeID')->references('Id')->on('t_HREmployees')->onDelete('cascade');
        });

        Schema::create('t_HRTrainingSessionFeedback', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('SessionID');
            $table->unsignedBigInteger('EmployeeID');
            $table->unsignedTinyInteger('RatingContent')->nullable();
            $table->unsignedTinyInteger('RatingTrainer')->nullable();
            $table->unsignedTinyInteger('RatingRelevance')->nullable();
            $table->text('Comments')->nullable();
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->unique(['SessionID', 'EmployeeID'], 'ux_hr_training_session_feedback');
            $table->foreign('SessionID')->references('Id')->on('t_HRTrainingSessions')->onDelete('cascade');
            $table->foreign('EmployeeID')->references('Id')->on('t_HREmployees')->onDelete('cascade');
        });

        Schema::create('t_HRTrainingCertificates', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('SessionID')->nullable();
            $table->unsignedBigInteger('EmployeeID');
            $table->string('CertificationName', 200);
            $table->string('IssuingBody', 150)->nullable();
            $table->string('CertificateNumber', 50)->nullable();
            $table->date('IssuedOn')->nullable();
            $table->date('ExpiresOn')->nullable();
            $table->unsignedBigInteger('DocumentId')->nullable();
            $table->string('Status', 30)->default('Valid');
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->foreign('SessionID')->references('Id')->on('t_HRTrainingSessions')->onDelete('set null');
            $table->foreign('EmployeeID')->references('Id')->on('t_HREmployees')->onDelete('cascade');
            $table->foreign('DocumentId')->references('Id')->on('t_Documents');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HRTrainingCertificates');
        Schema::dropIfExists('t_HRTrainingSessionFeedback');
        Schema::dropIfExists('t_HRTrainingSessionParticipants');
        Schema::dropIfExists('t_HRTrainingSessions');
        Schema::dropIfExists('t_HRTrainingProgramTargets');
        Schema::dropIfExists('t_HRTrainingPrograms');
        Schema::dropIfExists('t_HRTrainingTrainers');
        Schema::dropIfExists('t_HRTrainingCategories');
    }
};
