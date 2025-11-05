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
        Schema::create('t_PettyCashFloats', function (Blueprint $table) {
            $table->bigIncrements('FloatID');
            $table->string('Code', 50)->unique();
            $table->string('Name', 150);
            $table->bigInteger('CurrencyID');
            $table->bigInteger('CustodianUserID')->nullable();
            $table->decimal('FloatLimit', 18)->default(0);
            $table->decimal('ReorderLevel', 18)->default(0);
            $table->boolean('IsActive')->default(true)->index();
            $table->decimal('OpeningBalance', 18)->default(0);
            $table->dateTime('CreatedOn')->useCurrent();
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->boolean('RequireApproval')->default(false);
            $table->decimal('ApprovalLimit', 18)->default(0);

            $table->primary(['FloatID'], 'pk__t_pettyc__63abceda602a801c');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_PettyCashFloats');
    }
};
