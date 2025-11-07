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
        Schema::create('t_DocumentCheckOuts', static function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('DocumentId')->constrained('t_Documents', 'Id');
            $table->longText('CheckOutRemark')->nullable();
            $table->longText('CheckInRemark')->nullable();
            $table->char('Status', 3)->comment('DocumentCheckOutStatus');
            $table->dateTime('Dated');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        Schema::table('t_DocumentAttributes', static function (Blueprint $table) {
            $table->foreignId('VersionId')->nullable()->after('DocumentId')->constrained('t_DocumentVersions', 'Id')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_DocumentAttributes', static function (Blueprint $table) {
            $table->dropConstrainedForeignId('VersionId');
        });

        Schema::dropIfExists('t_DocumentCheckOuts');
    }
};
