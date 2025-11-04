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
        Schema::create('t_Emails', function (Blueprint $table) {
            $table->bigIncrements('EmailID');
            $table->string('MailID')->nullable();
            $table->char('Type', 1)->default('o');
            $table->char('Status', 1)->default('q');
            $table->char('Priority', 1)->default('n');
            $table->string('From')->default('temp@craftsilicon.com');
            $table->text('To');
            $table->text('CC')->nullable();
            $table->text('BCC')->nullable();
            $table->string('Subject');
            $table->text('Body');
            $table->text('Text');
            $table->string('Party')->nullable();
            $table->string('PartyID', 100)->nullable();
            $table->text('Extra')->nullable();
            $table->string('Source')->nullable();
            $table->string('SourceID', 100)->nullable();
            $table->dateTime('Dated')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('EmailConversationId')->nullable();
            $table->string('ReferenceId')->nullable();
            $table->bigInteger('ReadBy')->nullable();
            $table->dateTime('ReadOn')->nullable();

            $table->primary(['EmailID'], 'pk__t_emails__7ed91aef0c0f15a2');
            $table->index(['Party', 'PartyID']);
            $table->index(['Source', 'SourceID']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Emails');
    }
};
