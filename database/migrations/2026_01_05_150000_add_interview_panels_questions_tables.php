<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_HRJobInterviews', function (Blueprint $table) {
            if (!Schema::hasColumn('t_HRJobInterviews', 'JobOpeningID')) {
                $table->unsignedBigInteger('JobOpeningID')->nullable()->after('ApplicationID');
            }
        });

        Schema::create('t_HRJobInterviewPanels', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('InterviewID');
            $table->unsignedBigInteger('EmployeeID');
            $table->string('Role', 100)->nullable();
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();

            $table->foreign('InterviewID')->references('Id')->on('t_HRJobInterviews')->onDelete('cascade');
            $table->foreign('EmployeeID')->references('Id')->on('t_HREmployees');
        });

        Schema::create('t_HRJobInterviewQuestionGroups', function (Blueprint $table) {
            $table->id('Id');
            $table->string('Name', 150);
            $table->text('Description')->nullable();
            $table->boolean('IsActive')->default(1);
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
        });

        Schema::create('t_HRJobInterviewQuestions', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('GroupID')->nullable();
            $table->string('Title', 255);
            $table->text('Guidance')->nullable();
            $table->boolean('IsActive')->default(1);
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();

            $table->foreign('GroupID')->references('Id')->on('t_HRJobInterviewQuestionGroups');
        });

        Schema::create('t_HRJobOpeningQuestions', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('JobOpeningID');
            $table->unsignedBigInteger('QuestionID');
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();

            $table->foreign('JobOpeningID')->references('Id')->on('t_HRJobOpenings')->onDelete('cascade');
            $table->foreign('QuestionID')->references('Id')->on('t_HRJobInterviewQuestions')->onDelete('cascade');
        });

        Schema::create('t_HRJobInterviewQuestionAssignments', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('InterviewID');
            $table->unsignedBigInteger('QuestionID');
            $table->unsignedBigInteger('PanelistID')->nullable();
            $table->unsignedBigInteger('AssignedBy')->nullable();
            $table->dateTime('AssignedOn')->useCurrent();

            $table->foreign('InterviewID')->references('Id')->on('t_HRJobInterviews')->onDelete('cascade');
            $table->foreign('QuestionID')->references('Id')->on('t_HRJobInterviewQuestions')->onDelete('cascade');
            $table->foreign('PanelistID')->references('Id')->on('t_HREmployees');
        });

        Schema::create('t_HRJobInterviewScores', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('InterviewID');
            $table->unsignedBigInteger('QuestionID');
            $table->unsignedBigInteger('PanelistID')->nullable();
            $table->decimal('Score', 10, 2)->nullable();
            $table->text('Comment')->nullable();
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();

            $table->foreign('InterviewID')->references('Id')->on('t_HRJobInterviews')->onDelete('cascade');
            $table->foreign('QuestionID')->references('Id')->on('t_HRJobInterviewQuestions')->onDelete('cascade');
            $table->foreign('PanelistID')->references('Id')->on('t_HREmployees');
        });

        Schema::create('t_HRInterviewNotifications', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('InterviewID');
            $table->unsignedBigInteger('EmployeeID');
            $table->string('Message', 255);
            $table->dateTime('CreatedOn')->useCurrent();

            $table->foreign('InterviewID')->references('Id')->on('t_HRJobInterviews')->onDelete('cascade');
            $table->foreign('EmployeeID')->references('Id')->on('t_HREmployees');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HRInterviewNotifications');
        Schema::dropIfExists('t_HRJobInterviewScores');
        Schema::dropIfExists('t_HRJobInterviewQuestionAssignments');
        Schema::dropIfExists('t_HRJobOpeningQuestions');
        Schema::dropIfExists('t_HRJobInterviewQuestions');
        Schema::dropIfExists('t_HRJobInterviewQuestionGroups');
        Schema::dropIfExists('t_HRJobInterviewPanels');

        Schema::table('t_HRJobInterviews', function (Blueprint $table) {
            if (Schema::hasColumn('t_HRJobInterviews', 'JobOpeningID')) {
                $table->dropColumn('JobOpeningID');
            }
        });
    }
};
