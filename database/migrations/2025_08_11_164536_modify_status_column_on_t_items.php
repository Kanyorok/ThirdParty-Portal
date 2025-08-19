<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_Items', function (Blueprint $table) {
            // Drop the old boolean Status column
            $table->dropColumn('Status');

            // Add StatusId referencing t_CodeDetails
            $table->foreignId('Status')->nullable()->constrained('t_CodeDetails', 'ID');
        });
    }

    public function down(): void
    {
        Schema::table('t_Items', function (Blueprint $table) {
            // Remove foreign key and column
            $table->dropForeign(['Status']);
            $table->dropColumn('Status');

            // Restore the original boolean Status column
            $table->boolean('Status')->default(true)->after('DocumentUpload')->comment('status: Active or Inactive');
        });
    }
};
