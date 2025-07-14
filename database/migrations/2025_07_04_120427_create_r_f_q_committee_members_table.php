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
        Schema::create('t_RFQCommitteeMembers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('CommitteeID')->constrained('t_RFQCommittee', 'Id');
            $table->foreignId('UserID')->constrained('t_Users', 'Id');
            $table->foreignId('RFQID')->constrained('t_RFQ', 'Id');
            $table->string('Role', 100)->nullable();
            $table->unsignedTinyInteger('Response')->default(0);
            $table->boolean('IsActive')->default(true);
            $table->boolean('HasEvaluated')->default(false)->nullable();
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
        Schema::dropIfExists('t_RFQCommitteeMembers');
    }
};
