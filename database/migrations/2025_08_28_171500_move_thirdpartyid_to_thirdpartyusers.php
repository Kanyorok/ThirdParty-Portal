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
        // If an incorrect ThirdPartyId was added to t_ThirdParties, drop it and its FK
        Schema::table('t_ThirdParties', function (Blueprint $table) {
            if (Schema::hasColumn('t_ThirdParties', 'ThirdPartyId')) {
                try {
                    $table->dropForeign('fk_t_thirdparties_thirdpartyid');
                } catch (\Exception $e) {
                    // ignore if constraint name differs or missing
                }

                try {
                    $table->dropColumn('ThirdPartyId');
                } catch (\Exception $e) {
                    // ignore if already removed
                }
            }
        });

        // Add ThirdPartyId to t_ThirdPartyUsers (the model expects this column)
        Schema::table('t_ThirdPartyUsers', function (Blueprint $table) {
            if (!Schema::hasColumn('t_ThirdPartyUsers', 'ThirdPartyId')) {
                $table->unsignedBigInteger('ThirdPartyId')->nullable()->after('Id');

                // Add foreign key referencing t_ThirdParties(Id)
                $table->foreign('ThirdPartyId', 'fk_t_thirdpartyusers_thirdpartyid')
                    ->references('Id')
                    ->on('t_ThirdParties')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_ThirdPartyUsers', function (Blueprint $table) {
            if (Schema::hasColumn('t_ThirdPartyUsers', 'ThirdPartyId')) {
                try {
                    $table->dropForeign('fk_t_thirdpartyusers_thirdpartyid');
                } catch (\Exception $e) {
                    // ignore
                }

                try {
                    $table->dropColumn('ThirdPartyId');
                } catch (\Exception $e) {
                    // ignore
                }
            }
        });

        // Note: we intentionally do not re-create the incorrect column on t_ThirdParties here.
    }
};
