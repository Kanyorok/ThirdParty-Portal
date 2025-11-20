<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('t_BidSubmissions', function (Blueprint $table) {
            // Supplier relationship
            $table->bigInteger('SupplierId')->nullable()->after('SupplierName');

            // Document encryption fields
            $table->text('EncryptedDocuments')->nullable()->after('Documents'); // JSON of encrypted document info
            $table->string('EncryptionKey', 255)->nullable()->after('EncryptedDocuments'); // Encryption key

            // Submission tracking
            $table->enum('SubmissionSource', ['manual', 'portal'])->default('manual')->after('Remarks');
            $table->boolean('DocumentsAccessible')->default(false)->after('SubmissionSource');
            $table->dateTime('BidOpeningDate')->nullable()->after('DocumentsAccessible');

            // Add foreign key constraint for SupplierId if you want
            // $table->foreign('SupplierId')->references('Id')->on('t_Suppliers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_BidSubmissions', function (Blueprint $table) {
            // Drop columns in reverse order
            $table->dropColumn([
                'BidOpeningDate',
                'DocumentsAccessible',
                'SubmissionSource',
                'EncryptionKey',
                'EncryptedDocuments',
                'SupplierId'
            ]);
        });
    }
};
