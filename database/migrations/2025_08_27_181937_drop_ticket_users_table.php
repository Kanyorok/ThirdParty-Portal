<?php

use App\Enums\Core\RoleEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('t_TicketUsers');
        Schema::table('t_Tickets', static function (Blueprint $table) {
            $table->char('Visibility', 3)->default('pub')->comment('pub,pri')->after('TicketID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_Tickets', static function (Blueprint $table) {
            $table->dropColumn('Visibility');
        });

        Schema::create('t_TicketUsers', static function (Blueprint $table) {
            $table->id('Id');
            $table->string("Party");
            $table->string("PartyID", 100);
            $table->foreignId('TicketID')->constrained('t_Tickets', 'Id');
            $table->char('Role', '1')->default(RoleEnum::Read->value);
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');

            $table->index(["Party", "PartyID"]); //user/team
        });
    }
};

