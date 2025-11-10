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
        Schema::create('t_Reviews', static function (Blueprint $table) {
            $table->id('Id');
            $table->string('BranchID')->index()->nullable();
            $table->string("Party");
            $table->string("PartyID", 100)->nullable();
            $table->string("Source");//string or related.
            $table->string("SourceID", 100)->nullable();
            $table->smallInteger('Rating')->nullable();
            $table->longText("Content")->nullable();
            $table->char('Tonality', 2);
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');

            $table->index(["Source", "SourceID"]);
            $table->index(["Party", "PartyID"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Reviews');
    }
};
