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
        Schema::create('t_LeadUsers', static function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('LeadId')->constrained('t_Leads', 'LeadID')->cascadeOnDelete();
            $table->string("Party");
            $table->string("PartyID", 100);
            $table->char('Role', '1')->default(RoleEnum::Read->value);
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');

            $table->index(["Party", "PartyID"]); //user/team

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_LeadUsers');
    }
};
