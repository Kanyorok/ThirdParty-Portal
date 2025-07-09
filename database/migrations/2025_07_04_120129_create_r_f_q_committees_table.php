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
        Schema::create('t_RFQCommittee', function (Blueprint $table) {
            $table->Id();
            $table->foreignId('RFQID')->constrained('t_RFQ', 'Id'); // FK to t_RFQ
            $table->string('CommitteeName', 100)->nullable();
            $table->date('AppointmentDate')->nullable();
            $table->boolean('IsActive')->default(true);
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
        Schema::dropIfExists('t_RFQCommittee');
    }
};