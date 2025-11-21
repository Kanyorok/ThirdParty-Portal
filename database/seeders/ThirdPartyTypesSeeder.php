<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ThirdPartyTypesSeeder extends Seeder
{
    public function run(): void
    {
        // Fetch FinanceRoleIDs for Creditor (used for Supplier) and Debtor for Tenant per spec
        $creditorRoleId = DB::table('t_FinanceRoles')->where('RoleName', 'Creditor')->value('FinanceRoleID');
        $debtorRoleId = DB::table('t_FinanceRoles')->where('RoleName', 'Debtor')->value('FinanceRoleID');

        if (!$creditorRoleId || !$debtorRoleId) {
            $this->command?->warn('Creditor or Debtor finance role not found; skipping ThirdPartyTypes seeding.');
            return;
        }

        $now = now();
        $userId = 1; // system/admin user

        // Generate sequential custom TypeIds (string) like TE-0001, SU-0001 etc.
        // Assuming TypeId column is numeric auto-increment in table definition; if not, adjust accordingly.
        // We'll insert with explicit TypeId only if it is not auto-increment; otherwise rely on DB and store code in a Code column.
        // Since original request: "generate TypeId as unique tenant starts with TE-... and supplier SU-..." we'll use a Code column fallback.
        // If the table lacks a Code column, we insert into TypeId as string; ensure schema supports it.

        $existingColumns = DB::getSchemaBuilder()->getColumnListing('t_ThirdPartyTypes');
        $useCodeColumn = in_array('Code', $existingColumns); // will be true after migration adding Code

        $tenantCode = 'TE-0001';
        $supplierCode = 'SU-0001';

        $tenantDescription = 'Tenant';
        $supplierDescription = 'Supplier';

        // Resolve CategoryMaster IDs for Tenant (Name=Tenant, Type=TenantCategory) and Supplier (Name=Supplier, Type=SupplierCategory)
        $tenantCategoryId = DB::table('t_CategoryMaster')
            ->where('Name', 'Tenant')
            ->where('Type', 'TenantCategory')
            ->value('Id');
        $supplierCategoryId = DB::table('t_CategoryMaster')
            ->where('Name', 'Supplier')
            ->where('Type', 'SupplierCategory')
            ->value('Id');

        if (!$tenantCategoryId || !$supplierCategoryId) {
            $this->command?->warn('Required CategoryMaster records (Tenant/Supplier) missing; run CategoryMasterSeeder first.');
        }

        // Helper closure to upsert single record
        $upsert = function (string $code, ?int $categoryId,string $codeDesc) use ($useCodeColumn, $now, $userId, $creditorRoleId, $debtorRoleId) {
            $table = DB::table('t_ThirdPartyTypes');
            if ($useCodeColumn) {
                $exists = $table->where('Code', $code)->first();
                if ($exists) {
                    $table->where('Code', $code)->update([
                        'FinanceRole' => $debtorRoleId,
                        'Type' => $categoryId,
                        'ModifiedBy' => $userId,
                        'ModifiedOn' => $now,
                    ]);
                } else {
                    $table->insert([
                        'Code' => $code,
                        'Type' => $categoryId,
                        'FinanceRole' => $creditorRoleId,
                        'Description' => $codeDesc,
                        'CreatedBy' => $userId,
                        'ModifiedBy' => $userId,
                        'CreatedOn' => $now,
                        'ModifiedOn' => $now,
                    ]);
                }
            } else {
                // Should not happen now, but fallback to prevent failure
                $this->command?->warn('Code column missing on t_ThirdPartyTypes; create the migration to add it.');
            }
        };

        $upsert($tenantCode, $tenantCategoryId,$tenantDescription);
        $upsert($supplierCode, $supplierCategoryId,$supplierDescription);

        //Add manually the Descriptions
        DB::table('t_ThirdPartyTypes')->where('Code', $tenantCode)->update(['Description' => 'Tenant']);
        DB::table('t_ThirdPartyTypes')->where('Code', $supplierCode)->update(['Description' => 'Supplier']);
    }
}
