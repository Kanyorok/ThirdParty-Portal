<?php

use App\Enums\Property\PropertyNewLeaseEnum;
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
        // t_LeaseCreation - add new columns
        Schema::table('t_LeaseCreation', static function (Blueprint $table) {
            $table->float('ServiceCharge')->nullable();
            $table->float('ParkingFee')->nullable();
            $table->float('OtherCharges')->nullable();
            $table->string('Status', 1)->nullable()->default(PropertyNewLeaseEnum::New->value);
        });

        // t_ScheduleLease - drop foreign keys & columns, add IsActive
        Schema::table('t_ScheduleLease', static function (Blueprint $table) {
            // Drop foreign key constraints
            $table->dropForeign(['TenantId']);
            $table->dropForeign(['PropertyId']);

            // Drop the actual columns
            $table->dropColumn(['TenantId', 'PropertyId']);

            // Add IsActive column
            $table->boolean('IsActive')->nullable()->default(1);
        });

        // t_RenewLease - drop foreign keys & add new columns
        Schema::table('t_RenewLease', static function (Blueprint $table) {
            // Drop foreign key constraints
            $table->dropForeign(['TenantId']);
            $table->dropForeign(['PropertyId']);

            // Drop the columns
            $table->dropColumn(['TenantId', 'PropertyId']);

            // Add new columns
            $table->float('ServiceCharge')->nullable();
            $table->float('ParkingFee')->nullable();
            $table->float('OtherCharges')->nullable();
            $table->boolean('IsActive')->nullable()->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // t_LeaseCreation - rollback
        Schema::table('t_LeaseCreation', static function (Blueprint $table) {
            $table->dropColumn(['ServiceCharge', 'ParkingFee', 'OtherCharges', 'Status']);
        });

        // t_ScheduleLease - rollback
        Schema::table('t_ScheduleLease', static function (Blueprint $table) {
            $table->unsignedBigInteger('TenantId')->nullable();
            $table->unsignedBigInteger('PropertyId')->nullable();
            $table->dropColumn('IsActive');

            // Re-add foreign keys (assuming foreign tables exist)
            $table->foreign('TenantId')->references('Id')->on('t_NewTenants')->onDelete('cascade');
            $table->foreign('PropertyId')->references('Id')->on('t_Properties')->onDelete('cascade');
        });

        // t_RenewLease - rollback
        Schema::table('t_RenewLease', static function (Blueprint $table) {
            $table->unsignedBigInteger('TenantId')->nullable();
            $table->unsignedBigInteger('PropertyId')->nullable();
            $table->dropColumn(['ServiceCharge', 'ParkingFee', 'OtherCharges', 'IsActive']);

            // Re-add foreign keys
            $table->foreign('TenantId')->references('Id')->on('t_NewTenants')->onDelete('cascade');
            $table->foreign('PropertyId')->references('Id')->on('t_Properties')->onDelete('cascade');
        });
    }
};
