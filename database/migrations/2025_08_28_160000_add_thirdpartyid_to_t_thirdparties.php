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
        Schema::table('t_ThirdParties', function (Blueprint $table) {
            if (!Schema::hasColumn('t_ThirdParties', 'ThirdPartyId')) {
                // Add nullable FK column
                $table->unsignedBigInteger('ThirdPartyId')->nullable()->after('Id');

                // Add foreign key constraint referencing t_ThirdPartyUsers(Id)
                $table->foreign('ThirdPartyId', 'fk_t_thirdparties_thirdpartyid')
                    ->references('Id')
                    ->on('t_ThirdPartyUsers')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_ThirdParties', function (Blueprint $table) {
            if (Schema::hasColumn('t_ThirdParties', 'ThirdPartyId')) {
                // Drop foreign key first, then column
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                try {
                    $table->dropForeign('fk_t_thirdparties_thirdpartyid');
                } catch (\Exception $e) {
                    // ignore if missing
                }

                $table->dropColumn('ThirdPartyId');
            }
        });
    }
};
