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
        Schema::create('t_ChequeBooks', function (Blueprint $table) {
            $table->bigIncrements('ChequeBookID');
            $table->bigInteger('BankAccountID');
            $table->string('BookName', 100)->nullable();
            $table->string('Prefix', 20)->nullable();
            $table->string('Suffix', 20)->nullable();
            $table->bigInteger('StartNumber');
            $table->bigInteger('EndNumber');
            $table->bigInteger('NextLeafNumber');
            $table->integer('LeavesTotal');
            $table->integer('LeavesIssued')->default(0);
            $table->boolean('IsActive')->default(true);
            $table->dateTime('CreatedOn')->useCurrent();
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();

            $table->primary(['ChequeBookID'], 'pk__t_cheque__07eec1dfe444ba79');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ChequeBooks');
    }
};
