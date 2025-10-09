<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
        Schema::create('t_RFQClarifications', function (Blueprint $table) {
			$table->id('Id');
			$table->foreignId('RFQId')->constrained('t_RFQ', 'Id')->cascadeOnDelete();
			$table->foreignId('SupplierId')->constrained('t_Suppliers', 'Id')->cascadeOnDelete();
            // Avoid multiple cascade paths: keep default NO ACTION by defining FK without delete clause
            $table->foreignId('RFQLineId')->nullable();
            $table->foreign('RFQLineId')->references('Id')->on('t_RFQLines');
			$table->text('Question');
			$table->text('Answer')->nullable();
			$table->foreignId('CreatedBy')->nullable()->constrained('t_Users', 'Id');
			$table->timestamp('CreatedOn')->nullable();
			$table->foreignId('ModifiedBy')->nullable()->constrained('t_Users', 'Id');
			$table->timestamp('ModifiedOn')->nullable();
			$table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
			$table->softDeletes('DeletedOn');
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('t_RFQClarifications');
	}
};
