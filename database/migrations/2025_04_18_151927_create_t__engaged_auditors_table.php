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
        Schema::create('t_EngagedAuditors', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('SasraAuditorId')->constrained('t_SasraAuditors')->onDelete('cascade');
            $table->date('EngagementStartDate');
            $table->date('EngagementEndDate')->nullable();
            $table->string('EngagementStatus')->default('Engaged'); // Engaged, Completed, Terminated
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id')->comment('User who created the auditors');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id')->comment('User who last modified the auditors');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t__engaged_auditors');
    }
};
