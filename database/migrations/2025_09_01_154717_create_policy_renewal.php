<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_BancassurancePolicyRenewals', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('PolicyID')->constrained('t_BancassurancePolicies', 'Id');
            $table->date('RenewalDate');
            $table->date('NewStartDate');
            $table->date('NewEndDate');
            $table->string('Status')->constrained('t_CodeDetails','ID'); // Requested, Approved, Completed, Rejected
            $table->string('Notes')->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_BancassurancePolicyRenewals');
    }
};
