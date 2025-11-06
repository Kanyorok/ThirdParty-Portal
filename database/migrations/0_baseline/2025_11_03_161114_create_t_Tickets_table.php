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
        Schema::create('t_Tickets', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('TicketID', 100)->unique();
            $table->string('Title');
            $table->bigInteger('CategoryID')->nullable();
            $table->text('Notes')->nullable();
            $table->string('Party');
            $table->string('PartyID', 100);
            $table->string('Source');
            $table->string('SourceID', 100);
            $table->char('Status', 1);
            $table->char('Priority', 1)->default('N');
            $table->string('Owner');
            $table->string('OwnerID', 100);
            $table->dateTime('StartDate')->nullable();
            $table->dateTime('EndDate')->nullable();
            $table->dateTime('ClosedOn')->nullable();
            $table->string('SourceTicketID', 100)->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->char('Visibility', 3)->default('pub');

            $table->primary(['Id'], 'pk__t_ticket__3214ec07bee1e431');
            $table->index(['Owner', 'OwnerID']);
            $table->index(['Party', 'PartyID']);
            $table->index(['Source', 'SourceID']);
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
