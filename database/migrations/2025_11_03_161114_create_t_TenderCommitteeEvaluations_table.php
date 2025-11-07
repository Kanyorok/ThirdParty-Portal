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
        Schema::create('t_TenderCommitteeEvaluations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('CommitteeID');
            $table->bigInteger('TenderID');
            $table->bigInteger('MemberID');
            $table->bigInteger('SectionID');
            $table->bigInteger('CriteriaID');
            $table->decimal('MaxScore', 5)->default(0);
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->decimal('Score', 4)->nullable();
            $table->boolean('IsActive')->default(true);
            $table->integer('SupplierId')->nullable()->index();

            $table->primary(['id'], 'pk__t_tender__3213e83f92def181');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_TenderCommitteeEvaluations');
    }
};
