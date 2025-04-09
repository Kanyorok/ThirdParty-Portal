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
        Schema::create('t_CRMEmails', static function (Blueprint $table) {
            $table->id('EmailID');
            $table->string('MailID', 255)->nullable();
            $table->char('Type', 1)->default('o');
            $table->char('Status', 1)->default('q');
            $table->char('Priority', 1)->default('n');
            $table->string('From', 255)->default('temp@craftsilicon.com');
            $table->jsonb('To');
            $table->jsonb('CC')->nullable();
            $table->jsonb('BCC')->nullable();
            $table->string('Subject', 255);
            $table->longText('Body');
            $table->longText('Text');
            $table->string("Party")->nullable();
            $table->string("PartyID", 100)->nullable();
            $table->jsonb('Extra')->nullable();
            $table->string("Source")->nullable();
            $table->string("SourceID", 100)->nullable();
            $table->dateTime('Dated')->nullable();
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
        Schema::dropIfExists('t_CRMEmails');
    }
};
