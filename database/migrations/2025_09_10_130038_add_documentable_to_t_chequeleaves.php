<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_ChequeLeaves', function (Blueprint $table) {
            $table->id('LeafID');
            $table->unsignedBigInteger('ChequeBookID');
            $table->unsignedBigInteger('LeafNumber');        // numeric sequence
            $table->string('ChequeNumber', 50)->nullable();  // Prefix + LeafNumber + Suffix (prebuilt for convenience)

            // Unused | Reserved | Issued | Cleared | Bounced | Cancelled | Spoiled
            $table->string('Status', 20)->default('Unused');

            $table->unsignedBigInteger('ChequeID')->nullable(); // link once used
            $table->date('ReservedOn')->nullable();
            $table->date('UsedOn')->nullable();
            $table->date('ClearedOn')->nullable();
            $table->date('CancelledOn')->nullable();

            $table->string('Notes', 200)->nullable();

            // Audit (SQL Server style)
            $table->dateTime('CreatedOn')->default(DB::raw('GETDATE()'));
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();

            $table->foreign('ChequeBookID')->references('ChequeBookID')->on('t_ChequeBooks');

            $table->unique(['ChequeBookID', 'LeafNumber']);
            $table->unique(['ChequeBookID', 'ChequeNumber']);
            $table->index(['ChequeBookID', 'Status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_ChequeLeaves');
    }
};
