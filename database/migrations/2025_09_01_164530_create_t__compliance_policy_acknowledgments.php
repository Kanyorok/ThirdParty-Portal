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
        Schema::create('t_CompliancePolicyAcknowledgments', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('PolicyID')->constrained('t_CompliancePolicies', 'Id')->onDelete('cascade');
            $table->unsignedBigInteger('UserID'); // from t_Users
            $table->timestamp('AcknowledgedOn')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_CompliancePolicyAcknowledgments');
    }
};
