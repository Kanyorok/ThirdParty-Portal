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
        Schema::create('t_ComplianceCertifications', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('UserID');
            $table->string('CertificationName', 200);
            $table->date('IssueDate')->nullable();
            $table->date('ExpiryDate')->nullable();
            $table->string('Status', 50)->default('Active');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn')->useCurrent();

            $table->primary(['Id'], 'pk__t_compli__3214ec07660c888f');
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
