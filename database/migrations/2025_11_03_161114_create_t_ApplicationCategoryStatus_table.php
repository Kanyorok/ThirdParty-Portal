<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_ApplicationCategoryStatus', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('ApplicationId');
            $table->bigInteger('CategoryId');
            $table->char('Status', 1)->default('D');
            $table->decimal('ProgressPercent', 5)->default(0);
            $table->string('Stage', 100)->default('documentation');
            $table->string('StageLabel')->default('Documentation Phase');
            $table->dateTime('DecisionDate')->nullable();
            $table->text('RejectionReason')->nullable();
            $table->text('ReviewerNotes')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn')->useCurrent();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable()->useCurrent();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_applic__3214ec076ff8af39');
            $table->unique(['ApplicationId', 'CategoryId'], 'uq_application_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ApplicationCategoryStatus');
    }
};
