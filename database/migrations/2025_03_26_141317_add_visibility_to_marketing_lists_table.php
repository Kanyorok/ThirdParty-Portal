<?php

use App\Enums\Core\VisibilityEnum;
use App\Models\CRM\MarketingList;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('t_MarketingLists', static function (Blueprint $table) {
            $table->char('Visibility', 3)->default(VisibilityEnum::Private->value)->after('Type');
            $table->json('Processing')->nullable()->after('LastContacted');
        });

        MarketingList::query()->where('Source', \App\Models\BR\Account::getPrimaryKey())->update(['Source' => \App\Models\BR\DebtProduct::getPrimaryKey()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_MarketingLists', static function (Blueprint $table) {
            $table->dropColumn(['Visibility', 'Processing']);
        });
    }
};
