<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_HRLeaveBalances', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('EmployeeID');
            $table->unsignedBigInteger('LeaveTypeID');
            $table->decimal('Entitlement', 8, 2)->default(0);
            $table->decimal('Accrued', 8, 2)->default(0);
            $table->decimal('Taken', 8, 2)->default(0);
            $table->decimal('Balance', 8, 2)->default(0);
            $table->unsignedBigInteger('UpdatedBy')->nullable();
            $table->dateTime('UpdatedOn')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HRLeaveBalances');
    }
};
