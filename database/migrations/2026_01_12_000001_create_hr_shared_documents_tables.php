<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_HRSharedDocumentCategories', function (Blueprint $table) {
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

        Schema::create('t_HRSharedDocuments', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('CategoryID')->nullable();
            $table->unsignedBigInteger('ParentID')->nullable();
            $table->string('Title', 200);
            $table->text('Description')->nullable();
            $table->string('Version', 20)->nullable();
            $table->date('EffectiveDate')->nullable();
            $table->date('ExpiryDate')->nullable();
            $table->date('AcknowledgementDueOn')->nullable();
            $table->unsignedBigInteger('OwnerDepartmentID')->nullable();
            $table->string('AccessLevel', 30)->default('Public');
            $table->boolean('IsDownloadable')->default(1);
            $table->boolean('IsMandatory')->default(0);
            $table->string('Language', 30)->nullable();
            $table->string('Status', 30)->default('Draft');
            $table->unsignedBigInteger('DocumentId')->nullable();
            $table->boolean('IsActive')->default(1);
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->foreign('CategoryID')->references('Id')->on('t_HRSharedDocumentCategories');
            $table->foreign('ParentID')->references('Id')->on('t_HRSharedDocuments');
            $table->foreign('OwnerDepartmentID')->references('Id')->on('t_Departments');
            $table->foreign('DocumentId')->references('Id')->on('t_Documents');
        });

        Schema::create('t_HRSharedDocumentDepartments', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('DocumentID');
            $table->unsignedBigInteger('DepartmentID');
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();

            $table->unique(['DocumentID', 'DepartmentID'], 'ux_hr_shared_doc_dept');
            $table->foreign('DocumentID')->references('Id')->on('t_HRSharedDocuments')->onDelete('cascade');
            $table->foreign('DepartmentID')->references('Id')->on('t_Departments')->onDelete('cascade');
        });

        Schema::create('t_HRSharedDocumentRoles', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('DocumentID');
            $table->unsignedBigInteger('RoleID');
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();

            $table->unique(['DocumentID', 'RoleID'], 'ux_hr_shared_doc_role');
            $table->foreign('DocumentID')->references('Id')->on('t_HRSharedDocuments')->onDelete('cascade');
            $table->foreign('RoleID')->references('Id')->on('t_HRJobRoles')->onDelete('cascade');
        });

        Schema::create('t_HRSharedDocumentAcknowledgements', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('DocumentID');
            $table->unsignedBigInteger('EmployeeID');
            $table->string('Status', 30)->default('Pending');
            $table->date('DueOn')->nullable();
            $table->dateTime('AcknowledgedOn')->nullable();
            $table->unsignedBigInteger('AcknowledgedBy')->nullable();
            $table->string('AcknowledgedIP', 45)->nullable();
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->unique(['DocumentID', 'EmployeeID'], 'ux_hr_shared_doc_ack');
            $table->foreign('DocumentID')->references('Id')->on('t_HRSharedDocuments')->onDelete('cascade');
            $table->foreign('EmployeeID')->references('Id')->on('t_HREmployees')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HRSharedDocumentAcknowledgements');
        Schema::dropIfExists('t_HRSharedDocumentRoles');
        Schema::dropIfExists('t_HRSharedDocumentDepartments');
        Schema::dropIfExists('t_HRSharedDocuments');
        Schema::dropIfExists('t_HRSharedDocumentCategories');
    }
};
