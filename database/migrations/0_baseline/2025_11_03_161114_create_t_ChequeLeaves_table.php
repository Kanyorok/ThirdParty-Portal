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
        Schema::create('t_ChequeLeaves', function (Blueprint $table) {
            $table->bigIncrements('LeafID');
            $table->bigInteger('ChequeBookID');
            $table->bigInteger('LeafNumber');
            $table->string('ChequeNumber', 50)->nullable();
            $table->string('Status', 20)->default('Unused');
            $table->bigInteger('ChequeID')->nullable();
            $table->date('ReservedOn')->nullable();
            $table->date('UsedOn')->nullable();
            $table->date('ClearedOn')->nullable();
            $table->date('CancelledOn')->nullable();
            $table->string('Notes', 200)->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();

            $table->primary(['LeafID'], 'pk__t_cheque__7363146be33b91a0');
            $table->unique(['ChequeBookID', 'ChequeNumber']);
            $table->unique(['ChequeBookID', 'LeafNumber']);
            $table->index(['ChequeBookID', 'Status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ChequeLeaves');
    }
};
