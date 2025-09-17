<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_ApplicationCategoryStatus', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->unsignedBigInteger('ApplicationId');
            $table->unsignedBigInteger('CategoryId');
            $table->char('Status', 1)->default('D'); // D=Draft,S=Submitted,U=Under Review,C=Conditional,A=Approved,R=Rejected
            $table->decimal('ProgressPercent', 5, 2)->default(0.00);
            $table->string('Stage', 100)->default('documentation');
            $table->string('StageLabel', 255)->default('Documentation Phase');
            $table->dateTime('DecisionDate')->nullable();
            $table->text('RejectionReason')->nullable();
            $table->text('ReviewerNotes')->nullable();

            $table->unsignedBigInteger('CreatedBy');
            $table->timestamp('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->timestamp('ModifiedOn')->useCurrent()->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->timestamp('DeletedOn')->nullable();

            $table->unique(['ApplicationId','CategoryId'], 'UQ_Application_Category');
        });

        // FKs via statements to allow custom names and SQL Server
        DB::statement("ALTER TABLE dbo.t_ApplicationCategoryStatus ADD CONSTRAINT FK_AppCatStatus_Application FOREIGN KEY (ApplicationId) REFERENCES dbo.t_SupplierPrequalificationApplications(ApplicationID) ON DELETE CASCADE");
        DB::statement("ALTER TABLE dbo.t_ApplicationCategoryStatus ADD CONSTRAINT FK_AppCatStatus_Category FOREIGN KEY (CategoryId) REFERENCES dbo.t_SupplierCategories(SupplierCategoryID)");
    DB::statement("ALTER TABLE dbo.t_ApplicationCategoryStatus ADD CONSTRAINT FK_AppCatStatus_CreatedBy FOREIGN KEY (CreatedBy) REFERENCES dbo.t_ThirdPartyUsers(Id)");
    DB::statement("ALTER TABLE dbo.t_ApplicationCategoryStatus ADD CONSTRAINT FK_AppCatStatus_ModifiedBy FOREIGN KEY (ModifiedBy) REFERENCES dbo.t_ThirdPartyUsers(Id)");
    DB::statement("ALTER TABLE dbo.t_ApplicationCategoryStatus ADD CONSTRAINT FK_AppCatStatus_DeletedBy FOREIGN KEY (DeletedBy) REFERENCES dbo.t_ThirdPartyUsers(Id)");
    }

    public function down(): void
    {
        try { DB::statement("ALTER TABLE dbo.t_ApplicationCategoryStatus DROP CONSTRAINT FK_AppCatStatus_Application"); } catch (\Throwable $e) {}
        try { DB::statement("ALTER TABLE dbo.t_ApplicationCategoryStatus DROP CONSTRAINT FK_AppCatStatus_Category"); } catch (\Throwable $e) {}
        try { DB::statement("ALTER TABLE dbo.t_ApplicationCategoryStatus DROP CONSTRAINT FK_AppCatStatus_CreatedBy"); } catch (\Throwable $e) {}
        try { DB::statement("ALTER TABLE dbo.t_ApplicationCategoryStatus DROP CONSTRAINT FK_AppCatStatus_ModifiedBy"); } catch (\Throwable $e) {}
        try { DB::statement("ALTER TABLE dbo.t_ApplicationCategoryStatus DROP CONSTRAINT FK_AppCatStatus_DeletedBy"); } catch (\Throwable $e) {}
        Schema::dropIfExists('t_ApplicationCategoryStatus');
    }
};
