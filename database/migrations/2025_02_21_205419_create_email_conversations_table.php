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
        Schema::create('t_CRMEmailsConversations', static function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('Emails')->default(1)->comment('Number of Emails in Conversation');
            $table->foreignId('EmailId')->comment('Latest Email of Conversation')->constrained('t_CRMEmails', 'EmailID');
            $table->string("Party")->nullable();
            $table->string("PartyID", 100)->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');

            $table->index(["Party", "PartyID"]);
        });

        Schema::table('t_CRMEmails', static function (Blueprint $table) {
            $table->foreignId('EmailConversationId')->nullable()->after('Source')->constrained('t_CRMEmailsConversations', 'Id');
            $table->string('ReferenceId')->nullable()->after('EmailConversationId');
            $table->foreignId('ReadBy')->nullable()->after('Dated')->constrained('t_Users', 'Id');
            $table->dateTime('ReadOn')->nullable()->after('Dated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_CRMEmails', static function (Blueprint $table) {
            $table->dropConstrainedForeignId('EmailConversationId');
            $table->dropConstrainedForeignId('ReadBy');
            $table->dropColumn(['ReferenceId', 'ReadOn']);
        });

        Schema::dropIfExists('t_CRMEmailsConversations');
    }
};
