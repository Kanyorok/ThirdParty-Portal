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
        Schema::create('t_MarketingLists', static function (Blueprint $table) {
            $table->id('MarketingListID');
            $table->string('slug', 80)->unique();
            $table->string('Label', 200);
            $table->char('Type', 2);
            $table->string('Source', 40);
            $table->jsonb('Extra')->nullable();
            $table->longText('Notes')->nullable();
            $table->dateTime('LastContacted')->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_MarketingLists');
    }
};
