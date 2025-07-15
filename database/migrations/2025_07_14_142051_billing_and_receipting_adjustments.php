<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{

    public function up(): void
    {
        // Safely drop foreign key constraint if exists
        try {
            DB::statement('ALTER TABLE t_RentInvoice DROP CONSTRAINT FK_t_RentInvoice_TenantId');
        } catch (\Exception $e) {
            // Constraint doesn't exist – safe to ignore
        }

        // Drop columns only if they exist
        Schema::table('t_RentInvoice', function (Blueprint $table) {
            $columnsToDrop = ['TenantId', 'InvoiceDate', 'RentAmount', 'ServicesCharge', 'OtherCharges'];

            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('t_RentInvoice', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        // Re-add new columns (assumes they don't already exist)
        Schema::table('t_RentInvoice', function (Blueprint $table) {
            if (!Schema::hasColumn('t_RentInvoice', 'InvoiceDate')) {
                $table->date('InvoiceDate')->nullable();
            }
            if (!Schema::hasColumn('t_RentInvoice', 'RentAmount')) {
                $table->decimal('RentAmount', 20, 2)->nullable();
            }
            if (!Schema::hasColumn('t_RentInvoice', 'ServicesCharge')) {
                $table->decimal('ServicesCharge', 20, 2)->nullable();
            }
            if (!Schema::hasColumn('t_RentInvoice', 'OtherCharges')) {
                $table->decimal('OtherCharges', 20, 2)->nullable();
            }
            if (!Schema::hasColumn('t_RentInvoice', 'ParkingFee')) {
                $table->decimal('ParkingFee', 20, 2)->nullable();
            }
            if (!Schema::hasColumn('t_RentInvoice', 'Status')) {
                $table->string('Status',1)->nullable();
            }
        });
    }


public function down(): void
{
    Schema::table('t_RentInvoice', function (Blueprint $table) {
        // Add back the columns if they don't exist
        if (!Schema::hasColumn('t_RentInvoice', 'TenantId')) {
            $table->unsignedBigInteger('TenantId')->nullable();
        }
        if (!Schema::hasColumn('t_RentInvoice', 'InvoiceDate')) {
            $table->date('InvoiceDate')->nullable();
        }
        if (!Schema::hasColumn('t_RentInvoice', 'RentAmount')) {
            $table->decimal('RentAmount', 20, 2)->nullable();
        }
        if (!Schema::hasColumn('t_RentInvoice', 'ServicesCharge')) {
            $table->decimal('ServicesCharge', 20, 2)->nullable();
        }
        if (!Schema::hasColumn('t_RentInvoice', 'OtherCharges')) {
            $table->decimal('OtherCharges', 20, 2)->nullable();
        }

        // Drop ParkingFee column if it exists
        if (Schema::hasColumn('t_RentInvoice', 'ParkingFee')) {
            $table->dropColumn('ParkingFee');
        }
        if (Schema::hasColumn('t_RentInvoice', 'Status')) {
            $table->dropColumn('Status');
        }
    });

    // Only re-add foreign key if referenced table exists
    $tenantTableExists = DB::select("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 't_Tenants'");
    if (!empty($tenantTableExists)) {
        Schema::table('t_RentInvoice', function (Blueprint $table) {
            $table->foreign('TenantId')->references('Id')->on('t_Tenants');
        });
    }
}

};
