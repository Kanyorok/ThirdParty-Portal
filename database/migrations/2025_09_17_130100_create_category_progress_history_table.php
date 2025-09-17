<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_CategoryProgressHistory', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->unsignedBigInteger('ApplicationCategoryId');
            $table->string('PreviousStatus', 50)->nullable();
            $table->string('NewStatus', 50);
            $table->decimal('PreviousProgress', 5, 2)->default(0.00);
            $table->decimal('NewProgress', 5, 2);
            $table->unsignedBigInteger('ChangedBy')->nullable();
            $table->text('Notes')->nullable();

            $table->unsignedBigInteger('CreatedBy');
            $table->timestamp('CreatedOn')->useCurrent();
        });

        DB::statement("ALTER TABLE dbo.t_CategoryProgressHistory ADD CONSTRAINT FK_ProgHist_AppCat FOREIGN KEY (ApplicationCategoryId) REFERENCES dbo.t_ApplicationCategoryStatus(Id) ON DELETE CASCADE");
    DB::statement("ALTER TABLE dbo.t_CategoryProgressHistory ADD CONSTRAINT FK_ProgHist_ChangedBy FOREIGN KEY (ChangedBy) REFERENCES dbo.t_ThirdPartyUsers(Id)");
    DB::statement("ALTER TABLE dbo.t_CategoryProgressHistory ADD CONSTRAINT FK_ProgHist_CreatedBy FOREIGN KEY (CreatedBy) REFERENCES dbo.t_ThirdPartyUsers(Id)");
    }

    public function down(): void
    {
        try { DB::statement("ALTER TABLE dbo.t_CategoryProgressHistory DROP CONSTRAINT FK_ProgHist_AppCat"); } catch (\Throwable $e) {}
        try { DB::statement("ALTER TABLE dbo.t_CategoryProgressHistory DROP CONSTRAINT FK_ProgHist_ChangedBy"); } catch (\Throwable $e) {}
        try { DB::statement("ALTER TABLE dbo.t_CategoryProgressHistory DROP CONSTRAINT FK_ProgHist_CreatedBy"); } catch (\Throwable $e) {}
        Schema::dropIfExists('t_CategoryProgressHistory');
    }
};
