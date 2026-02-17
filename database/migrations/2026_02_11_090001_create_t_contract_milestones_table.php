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
        Schema::create('t_ContractMilestones', function (Blueprint $table) {
            $table->id('Id');
            $table->string('ContractSourceType', 20); // tender | rfq
            $table->unsignedBigInteger('ContractSourceID');
            $table->integer('MilestoneNo')->default(1);
            $table->string('Title');
            $table->text('Description')->nullable();
            $table->date('PlannedDueDate')->nullable();
            $table->enum('ValueType', ['PERCENT', 'FIXED'])->default('FIXED');
            $table->decimal('ValuePercent', 10, 4)->nullable();
            $table->decimal('ValueAmount', 18, 2)->nullable();
            $table->boolean('AcceptanceRequired')->default(true);
            $table->enum('Status', ['Draft', 'In Progress', 'Submitted', 'Accepted', 'Rejected', 'Waived'])->default('Draft');
            $table->foreignId('AcceptedBy')->nullable()->constrained('t_Users', 'Id');
            $table->dateTime('AcceptedOn')->nullable();
            $table->foreignId('WaivedBy')->nullable()->constrained('t_Users', 'Id');
            $table->dateTime('WaivedOn')->nullable();
            $table->text('WaiveReason')->nullable();
            $table->timestamps();

            $table->index(['ContractSourceType', 'ContractSourceID'], 'idx_contract_milestones_contract_ref');
            $table->index(['ContractSourceType', 'ContractSourceID', 'Status'], 'idx_contract_milestones_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ContractMilestones');
    }
};

