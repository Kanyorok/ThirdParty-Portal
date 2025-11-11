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
        Schema::create('t_SMS', static function (Blueprint $table) {
            $table->id('Id');
            $table->string('SMSId');
            $table->string('Phone')->nullable();
            $table->char('Status', 1)->default('q');
            $table->char('Type', 1)->default('o');
            $table->longText('Content');
            $table->string("Party")->nullable();
            $table->string("PartyID", 100)->nullable();
            $table->string("Source")->nullable();
            $table->string("SourceID", 100)->nullable();
            $table->dateTime('Dated')->nullable();
            $table->longText('Response')->nullable();
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
        Schema::dropIfExists('t_SMS');
    }
};
