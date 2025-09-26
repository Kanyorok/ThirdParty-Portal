<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_FinanceCreditHolds', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('CreditID')->constrained('t_FinanceCreditManagement', 'Id');
            $table->foreignId('CustomerID')->constrained('t_ThirdParties', 'Id');
            $table->decimal('Amount', 18, 2);
            $table->string('Reason', 120)->nullable();
            $table->string('Status', 20)->default('Active'); // Active, Released, Expired
            $table->dateTime('HeldOn')->default(DB::raw('GETDATE()'));
            $table->dateTime('ReleasedOn')->nullable();
            $table->unsignedBigInteger('ReferenceID')->nullable();
            $table->string('ReferenceType', 60)->nullable();

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
        Schema::dropIfExists('t_FinanceCreditHolds');
    }
};


