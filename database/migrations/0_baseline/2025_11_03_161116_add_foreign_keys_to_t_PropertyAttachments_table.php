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
        Schema::table('t_PropertyAttachments', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DocumentType'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['PropertyID'])->references(['Id'])->on('t_PropertyRegistry')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_PropertyAttachments', function (Blueprint $table) {
            $table->dropForeign('t_propertyattachments_createdby_foreign');
            $table->dropForeign('t_propertyattachments_deletedby_foreign');
            $table->dropForeign('t_propertyattachments_documenttype_foreign');
            $table->dropForeign('t_propertyattachments_modifiedby_foreign');
            $table->dropForeign('t_propertyattachments_propertyid_foreign');
        });
    }
};
