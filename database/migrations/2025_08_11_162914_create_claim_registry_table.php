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
        Schema::create('t_BancassuranceClaims ', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('PolicyId')->constrained('t_BancassurancePolicies','Id');
            $table->foreignId('ClaimType')->constrained('t_CodeDetails','ID');
            $table->string('ClaimReason');
            $table->decimal('ClaimAmount',10, 2);
            $table->date('ClaimDate');
            $table->string('Status');
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
        Schema::dropIfExists('t_BancassuranceClaims');
    }
};
