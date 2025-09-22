<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_RFQAward', function (Blueprint $table) {
            $table->id('Id');

            $table->foreignId('RFQId')->constrained('t_RFQ', 'Id');
            $table->foreignId('SupplierId')->constrained('t_Suppliers', 'Id');
            $table->text('Comments')->nullable();

            // Audit fields (no default timestamps)
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');

            $table->unique(['RFQId']); // one award per RFQ
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_RFQAward');
    }
};


