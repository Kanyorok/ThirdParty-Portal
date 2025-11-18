<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_WorkFlowSignaturePending', function (Blueprint $table) {
             $table->id('Id'); // Primary key (BIGINT AUTO_INCREMENT)
            $table->unsignedBigInteger('DocumentId'); // FK reference
            $table->string('DocType', 100); // e.g., 'PurchaseOrder', 'Invoice'
            $table->unsignedBigInteger('SignatureId'); // FK to signature or user
            $table->boolean('IsValidated')->default(false);
            $table->dateTime('CreatedOn')->default(DB::raw('GETDATE()'));

            // Foreign keys
            $table->foreign('DocumentId')
                  ->references('Id')->on('t_Documents')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            // $table->foreign('SignatureId')
            //       ->references('Id')->on('t_Signatures')
            //       ->onDelete('cascade')
            //       ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_WorkFlowSignaturePending');
    }
};
