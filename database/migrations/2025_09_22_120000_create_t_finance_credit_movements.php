<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_FinanceCreditMovements', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('CreditID')->constrained('t_FinanceCreditManagement', 'Id');
            $table->foreignId('CustomerID')->constrained('t_ThirdParties', 'Id');
            $table->string('MovementType', 40); // limit_set, limit_increase, limit_decrease, utilize, repayment, hold, release, adjust
            $table->decimal('Amount', 18, 2);   // positive numbers; sign semantics handled by type
            $table->string('ReferenceType', 60)->nullable(); // e.g., invoice, receipt, journal
            $table->unsignedBigInteger('ReferenceID')->nullable();
            $table->text('Notes')->nullable();
            $table->dateTime('EffectiveOn')->default(DB::raw('GETDATE()'));

            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn')->default(DB::raw('GETDATE()'));
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn')->default(DB::raw('GETDATE()'));
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_FinanceCreditMovements');
    }
};


