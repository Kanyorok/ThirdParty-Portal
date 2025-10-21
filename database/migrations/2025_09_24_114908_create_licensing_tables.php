<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Main Licenses table (guarded)
        if (!Schema::hasTable('t_Licenses')) {
            Schema::create('t_Licenses', function (Blueprint $table) {
                $table->id('Id');
                $table->string('LicenseId', 64)->unique();
                $table->text('PayloadJson');
                $table->string('SignatureBase64', 512);
                $table->string('PublicKeyId', 64);
                $table->tinyInteger('Status')->default(1)->comment('1=active, 0=revoked');
                $table->dateTime('CreatedOn')->default(DB::raw('SYSUTCDATETIME()'));
                $table->dateTime('LastValidatedOn')->nullable();
                $table->index(['Status', 'CreatedOn']);
                $table->index('LicenseId');
            });
        }

        // Instance fingerprinting table (guarded)
        if (!Schema::hasTable('t_Instance')) {
            Schema::create('t_Instance', function (Blueprint $table) {
                $table->id('Id');
                $table->uuid('DbGuid')->unique();
                $table->string('HostFingerprint', 256);
                $table->string('AppVersion', 32)->nullable();
                $table->dateTime('CreatedOn')->default(DB::raw('SYSUTCDATETIME()'));
                $table->dateTime('UpdatedOn')->nullable();
                $table->index('DbGuid');
            });
        }

        // License audit log (guarded)
        if (!Schema::hasTable('t_LicenseAudit')) {
            Schema::create('t_LicenseAudit', function (Blueprint $table) {
                $table->id('Id');
                $table->dateTime('EventAt')->default(DB::raw('SYSUTCDATETIME()'));
                $table->string('Event', 64);
                $table->string('Detail', 512)->nullable();
                $table->string('LicenseId', 64)->nullable();
                $table->string('UserAgent', 512)->nullable();
                $table->ipAddress('IpAddress')->nullable();
                $table->index(['EventAt', 'Event']);
                $table->index('LicenseId');
            });
        }

        // Add ModuleKey to existing t_Modules table if not exists
        if (!Schema::hasColumn('t_Modules', 'ModuleKey')) {
            Schema::table('t_Modules', function (Blueprint $table) {
                $table->string('ModuleKey', 32)->nullable()->after('ModuleID');
                $table->index('ModuleKey');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_LicenseAudit');
        Schema::dropIfExists('t_Instance');
        Schema::dropIfExists('t_Licenses');
        
        if (Schema::hasColumn('t_Modules', 'ModuleKey')) {
            Schema::table('t_Modules', function (Blueprint $table) {
                $table->dropColumn('ModuleKey');
            });
        }
    }
};
