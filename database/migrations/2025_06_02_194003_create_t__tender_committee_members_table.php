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
        Schema::create('t_TenderCommitteeMembers', function (Blueprint $table) {
            $table->Id();
            $table->foreignId('CommitteeID')->constrained('t_TenderCommittee', 'Id'); // FK to t_TenderCommittee
            $table->foreignId('UserID')->constrained('t_Users', 'Id'); // FK to t_Users
            $table->foreignId('TenderID')->constrained('t_Tenders', 'Id'); // FK to t_Tenders
            $table->string('Role', 50)->nullable(); // VARCHAR(50) for role in the committee
            $table->tinyInteger('Response')->default(0); // TINYINT for response (0 = Pending, 1 = Yes, 2 = Declned)
            $table->boolean('IsActive')->default(true); // BIT (boolean in Laravel)
            $table->boolean('HasEvaluated')->default(false);
            $table->text('reason')->nullable();

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
        Schema::dropIfExists('t_TenderCommitteeMembers');
    }
};
