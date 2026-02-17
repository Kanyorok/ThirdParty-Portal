<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_CRMTrainingCategories', function (Blueprint $table) {
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

        Schema::create('t_CRMTrainingTrainers', function (Blueprint $table) {
            $table->id('Id');
            $table->string('TrainerType', 30)->default('Internal');
            $table->unsignedBigInteger('UserID')->nullable();
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

            $table->foreign('UserID')->references('Id')->on('t_Users');
        });

        Schema::create('t_CRMTrainingPrograms', function (Blueprint $table) {
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

            $table->foreign('CategoryID')->references('Id')->on('t_CRMTrainingCategories');
        });

        Schema::create('t_CRMTrainingProgramTargets', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('ProgramID');
            $table->string('TargetType', 30);
            $table->string('TargetID', 50);
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->index(['TargetType', 'TargetID'], 'ix_crm_training_targets_type_id');
            $table->unique(['ProgramID', 'TargetType', 'TargetID'], 'ux_crm_training_targets');
            $table->foreign('ProgramID')->references('Id')->on('t_CRMTrainingPrograms')->onDelete('cascade');
        });

        Schema::create('t_CRMTrainingSessions', function (Blueprint $table) {
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

            $table->foreign('ProgramID')->references('Id')->on('t_CRMTrainingPrograms')->onDelete('cascade');
            $table->foreign('TrainerID')->references('Id')->on('t_CRMTrainingTrainers');
            $table->foreign('AgendaDocumentId')->references('Id')->on('t_Documents');
            $table->foreign('MaterialsDocumentId')->references('Id')->on('t_Documents');
        });

        Schema::create('t_CRMTrainingSessionParticipants', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('SessionID');
            $table->string('ClientID', 50);
            $table->string('EnrollmentMethod', 30)->default('CRM');
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

            $table->unique(['SessionID', 'ClientID'], 'ux_crm_training_session_participant');
            $table->foreign('SessionID')->references('Id')->on('t_CRMTrainingSessions')->onDelete('cascade');
        });

        Schema::create('t_CRMTrainingSessionFeedback', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('SessionID');
            $table->string('ClientID', 50);
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

            $table->unique(['SessionID', 'ClientID'], 'ux_crm_training_session_feedback');
            $table->foreign('SessionID')->references('Id')->on('t_CRMTrainingSessions')->onDelete('cascade');
        });

        Schema::create('t_CRMTrainingCertificates', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('SessionID')->nullable();
            $table->string('ClientID', 50);
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

            $table->foreign('SessionID')->references('Id')->on('t_CRMTrainingSessions')->onDelete('set null');
            $table->foreign('DocumentId')->references('Id')->on('t_Documents');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_CRMTrainingCertificates');
        Schema::dropIfExists('t_CRMTrainingSessionFeedback');
        Schema::dropIfExists('t_CRMTrainingSessionParticipants');
        Schema::dropIfExists('t_CRMTrainingSessions');
        Schema::dropIfExists('t_CRMTrainingProgramTargets');
        Schema::dropIfExists('t_CRMTrainingPrograms');
        Schema::dropIfExists('t_CRMTrainingTrainers');
        Schema::dropIfExists('t_CRMTrainingCategories');
    }
};
