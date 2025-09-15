<?php

use App\Models\Core\Country;
use App\Models\Core\Locality;
use App\Models\CRM\Lead;
use App\Models\PropertyManagement\PropertyRegistry;
use App\Models\ThirdParies\Competitor;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_Countries', static function (Blueprint $table) {
            $table->id('Id');
            $table->string('Name')->unique();
            $table->string('CountryCode', 2)->unique()->comment('ISO 3166-1 alpha-3');
            $table->string('PhoneCode', 4)->nullable();
            $table->string('Flag')->nullable();
            $table->foreignId('CurrencyId')->constrained('t_Currencies', 'Id');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        Schema::table('t_Leads', static function (Blueprint $table) {
            $table->foreignId('CountryId')->nullable()->constrained('t_Countries', 'Id');
            $table->unsignedBigInteger('LocationID')->nullable()->change();
        });

        Schema::table('t_Competitors', static function (Blueprint $table) {
            $table->foreignId('CountryId')->nullable()->constrained('t_Countries', 'Id');
            $table->unsignedBigInteger('LocationID')->nullable()->change();
        });

        Schema::table('t_PropertyRegistry', static function (Blueprint $table) {
            $table->foreignId('CountryId')->nullable()->constrained('t_Countries', 'Id');
            $table->foreignId('LocationId')->nullable()->constrained('t_Localities', 'Id');
            $table->dropConstrainedForeignId('TownCity');
            $table->dropColumn('Country');
            $table->renameColumn('AreaLocality', 'Address');
        });

        Schema::table('t_Localities', static function (Blueprint $table) {
            $table->foreignId('CountryId')->nullable()->constrained('t_Countries', 'Id');
            $table->string('LocationType', 100)->change();
        });

        // Seed data
        
        Lead::query()->update(['CountryId' => null, 'LocationID' => null]);
        Competitor::query()->update(['CountryId' => null, 'LocationID' => null]);
        PropertyRegistry::query()->update(['CountryId' => null, 'LocationId' => null]);

        DB::table('t_Localities')->delete();

        Schema::table('t_Localities', static function (Blueprint $table) {
            $table->foreignId('CountryId')->nullable(false)->change();
        });

        Artisan::call('db:seed', [
            '--class' => 'LocalitySeeder',
            '--force' => true
        ]);

        $country = Country::query()->where('CountryCode', 'KE')->first();

        $location = $country?->localities()->whereLike('Name', '%Nairobi%')->whereNotNull('LocalityID')->first();
        if (!$location instanceof Locality) {
            $location = $country?->localities()->whereNotNull('LocalityID')->first();
        }

        if (!$country instanceof Country) {
            dd($country);
        }        
        Lead::query()->update(['CountryId' => $country->Id, 'LocationID' => $location->ID]);
        Competitor::query()->update(['CountryId' => $country->Id, 'LocationID' => $location->ID]);
        PropertyRegistry::query()->update(['CountryId' => $country->Id, 'LocationId' => $location->ID]);

        Schema::table('t_Leads', static function (Blueprint $table) {
            $table->foreignId('CountryId')->nullable(false)->change();
            $table->unsignedBigInteger('LocationID')->nullable(false)->change();
        });

        Schema::table('t_Competitors', static function (Blueprint $table) {
            $table->foreignId('CountryId')->nullable(false)->change();
            $table->unsignedBigInteger('LocationID')->nullable(false)->change();
        });

        Schema::table('t_PropertyRegistry', static function (Blueprint $table) {
            $table->foreignId('CountryId')->nullable()->change();
            $table->foreignId('LocationId')->nullable()->change();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_PropertyRegistry', static function (Blueprint $table) {
            $table->dropConstrainedForeignId('CountryId');
            $table->dropConstrainedForeignId('LocationId');
            $table->string('Country')->nullable();
            $table->foreignId('TownCity')->nullable()->constrained('t_Localities', 'ID');
            $table->renameColumn('Address', 'AreaLocality');
        });

        Schema::table('t_Leads', static function (Blueprint $table) {
            $table->dropConstrainedForeignId('CountryId');
            $table->unsignedBigInteger('LocationID')->nullable(true)->change();
        });

        Schema::table('t_Competitors', static function (Blueprint $table) {
            $table->dropConstrainedForeignId('CountryId');
            $table->unsignedBigInteger('LocationID')->nullable(true)->change();
        });

        Lead::query()->update(['LocationID' => null]);
        Competitor::query()->update(['LocationID' => null]);
        // PropertyRegistry::query()->update([]);

        DB::table('t_Localities')->delete();

        Schema::table('t_Localities', static function (Blueprint $table) {
            $table->dropConstrainedForeignId('CountryId');
            $table->string('LocationType', 10)->change();
        });

        Schema::dropIfExists('t_Countries');
    }
};
