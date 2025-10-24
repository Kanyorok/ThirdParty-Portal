<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_ComplianceCertifications', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('UserID'); // from t_Users
            $table->string('CertificationName', 200);
            $table->date('IssueDate')->nullable();
            $table->date('ExpiryDate')->nullable();
            $table->string('Status', 50)->default('Active'); // Active / Expired

            $table->unsignedBigInteger('CreatedBy');
            $table->timestamp('CreatedOn')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ComplianceCertifications');
    }
};
