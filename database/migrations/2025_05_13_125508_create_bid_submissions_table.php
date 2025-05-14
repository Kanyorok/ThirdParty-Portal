<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_BidSubmissions', function (Blueprint $table) {
            $table->id('Id'); // Custom primary key as specified in the model
            $table->string('TenderRef'); // e.g., 'TND/PROC/2025/001'
            $table->string('SupplierName'); // e.g., 'Tech Supplies Ltd'
            $table->enum('SubmissionMode', ['Hand delivered', 'Courier', 'Email', 'Other']); // Submission mode options
            $table->dateTime('ReceivedAt'); // e.g., '2025-05-10 14:30:00'
            $table->string('RecordedBy'); // e.g., 'John Doe'
            $table->text('Remarks')->nullable(); // Optional remarks
            $table->text('Documents')->nullable(); // Optional list of documents (e.g., JSON or comma-separated)
            $table->timestamps(); // created_at and updated_at columns
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_BidSubmissions');
    }
};
