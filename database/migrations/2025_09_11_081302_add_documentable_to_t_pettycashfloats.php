<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_PettyCashFloats', function (Blueprint $table) {
            $table->id('FloatID');
            $table->string('Code', 50)->unique();
            $table->string('Name', 150);
            $table->unsignedBigInteger('CurrencyID');     // t_Currencies.Id
            $table->unsignedBigInteger('CustodianUserID')->nullable();
            $table->decimal('FloatLimit', 18, 2)->default(0);
            $table->decimal('ReorderLevel', 18, 2)->default(0);
            $table->boolean('IsActive')->default(1);

            // Optional: opening balance tracking
            $table->decimal('OpeningBalance', 18, 2)->default(0);

            // Audit
            $table->dateTime('CreatedOn')->default(DB::raw('GETDATE()'));
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();

            $table->foreign('CurrencyID')->references('Id')->on('t_Currencies');
            $table->index(['IsActive']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('t_PettyCashFloats');
    }
};
