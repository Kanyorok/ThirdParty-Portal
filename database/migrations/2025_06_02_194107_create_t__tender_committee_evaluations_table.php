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
            $table->Id();
            $table->foreignId('CommitteeID')->constrained('t_TenderCommittee', 'Id'); // FK to t_TenderCommittee
            $table->foreignId('TenderID')->constrained('t_Tenders', 'Id'); // FK to t_Tenders
            $table->foreignId('MemberID')->constrained('t_TenderCommitteeMembers', 'Id'); // FK to t_TenderCommitteeMembers
            $table->foreignId('SectionID')->constrained('t_Sections', 'Id'); // FK to t_Sections
            $table->foreignId(('CriteriaID'))->constrained('t_Criterias', 'Id'); // FK to t__criterias
            $table->decimal('MaxScore', 5, 2)->default(0.00); // DECIMAL(5,2)


            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
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
