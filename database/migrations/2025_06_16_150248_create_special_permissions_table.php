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
        Schema::create('t_SpecialPermissions', static function (Blueprint $table) {
            $table->id('Id');
            $table->char('Permission', 1)->default(RoleEnum::Read->value);
            $table->string("Party");
            $table->string("PartyID", 100);
            $table->string("Model");
            $table->string("ModelID", 100);
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');

            $table->index(["Model", "ModelID"]);//related
            $table->index(["Party", "PartyID"]); //user/team
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_SpecialPermissions');
    }
};
