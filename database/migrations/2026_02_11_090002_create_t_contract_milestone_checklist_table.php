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
        Schema::create('t_ContractMilestoneChecklist', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('MilestoneID')
                ->constrained('t_ContractMilestones', 'Id')
                ->cascadeOnDelete();
            $table->string('ItemDescription');
            $table->boolean('Required')->default(true);
            $table->boolean('IsFulfilled')->default(false);
            $table->foreignId('FulfilledBy')->nullable()->constrained('t_Users', 'Id');
            $table->dateTime('FulfilledOn')->nullable();
            $table->text('Notes')->nullable();
            $table->unsignedBigInteger('EvidenceDocId')->nullable();
            $table->timestamps();

            $table->index(['MilestoneID', 'Required', 'IsFulfilled'], 'idx_contract_checklist_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ContractMilestoneChecklist');
    }
};

