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
        Schema::create('t_Tickets', static function (Blueprint $table) {
            $table->id('Id');
            $table->string('TicketID', 100)->unique();
            $table->string('Title');
            $table->foreignId('CategoryID')->nullable()->comment('TicketCategories')->constrained('t_CRMCodeDetails', 'ID');
            $table->longText('Notes')->nullable();
            $table->string("Party");
            $table->string("PartyID", 100);
            $table->string("Source");//source with reference if available
            $table->string("SourceID", 100);
            $table->char('Status', 1);
            $table->char('Priority', 1)->default(\App\Enums\TicketPriorityEnum::Normal->value);
            $table->string("Owner");
            $table->string("OwnerID", 100);
            $table->dateTime('StartDate')->nullable();
            $table->dateTime('EndDate')->nullable();
            $table->dateTime('ClosedOn')->nullable();
            $table->string('SourceTicketID', 100)->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');

            $table->index(['Owner', 'OwnerID']);
            $table->index(["Party", "PartyID"]);
            $table->index(["Source", "SourceID"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Tickets');
    }
};
