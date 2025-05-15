<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_BidSubmissions', function (Blueprint $table) {
            $table->string('TenderRef'); // e.g., 'TND/PROC/2025/001'
            $table->string('SupplierName'); // e.g., 'Tech Supplies Ltd'
            $table->foreignId('SubmissionMode')->constrained('t_CodeDetails', 'ID');
            $table->dateTime('ReceivedAt'); // e.g., '2025-05-10 14:30:00'
            $table->text('Remarks')->nullable(); // Optional remarks
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_BidSubmissions');
    }
};
