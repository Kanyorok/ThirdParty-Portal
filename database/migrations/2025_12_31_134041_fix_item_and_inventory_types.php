<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Core\Approval\CodeDetail;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
    //     // 1. Ensure Item Types exist
    //     $stockTypeId = $this->ensureItemType('Stock');
    //     $intangibleTypeId = $this->ensureItemType('Intangible');

    //     // 2. Ensure Inventory Type 'Durable' exists
    //     $durableInventoryTypeId = $this->ensureInventoryType('Durable');

    //     // 3. Update all existing items
    //     DB::table('t_Items')
    //         ->whereNull('DeletedOn')
    //         ->update([
    //             'InventoryType' => $durableInventoryTypeId,
    //             'ItemType' => $stockTypeId
    //         ]);

    //     echo "Updated items with ItemType: Stock ($stockTypeId) and InventoryType: Durable ($durableInventoryTypeId)\n";
    // }

    // private function ensureItemType($name)
    // {
    //     $codeDetailId = DB::table('t_CodeDetails')
    //         ->where('CodeID', 'ItemType')
    //         ->where('Description', $name)
    //         ->value('Id');

    //     if (!$codeDetailId) {
    //         $codeDetailId = DB::table('t_CodeDetails')->insertGetId([
    //             'CodeID' => 'ItemType',
    //             'Description' => $name,
    //             'Value' => strtolower($name),
    //             'DisplayOrder' => 1,
    //             'IsActive' => 1,
    //             'CreatedBy' => 1,
    //             'ModifiedBy' => 1,
    //             'CreatedOn' => now(),
    //             'ModifiedOn' => now(),
    //         ]);
    //     }

    //     $itemTypeId = DB::table('t_ItemTypes')
    //         ->where('TypeName', $codeDetailId)
    //         ->value('Id');

    //     if (!$itemTypeId) {
    //         $itemTypeId = DB::table('t_ItemTypes')->insertGetId([
    //             'TypeName' => $codeDetailId,
    //             'StockTracked' => 1,
    //             'RequiresTagging' => 0,
    //             'Active' => 1,
    //             'CreatedBy' => 1,
    //             'ModifiedBy' => 1,
    //             'CreatedOn' => now(),
    //             'ModifiedOn' => now(),
    //         ]);
    //     }

    //     return $itemTypeId;
    // }

    // private function ensureInventoryType($name)
    // {
    //     $codeDetailId = DB::table('t_CodeDetails')
    //         ->where('CodeID', 'InventoryType')
    //         ->where('Description', $name)
    //         ->value('Id');

    //     if (!$codeDetailId) {
    //         $codeDetailId = DB::table('t_CodeDetails')->insertGetId([
    //             'CodeID' => 'InventoryType',
    //             'Description' => $name,
    //             'Value' => strtolower($name),
    //             'DisplayOrder' => 1,
    //             'IsActive' => 1,
    //             'CreatedBy' => 1,
    //             'ModifiedBy' => 1,
    //             'CreatedOn' => now(),
    //             'ModifiedOn' => now(),
    //         ]);
    //     }

    //     $inventoryTypeId = DB::table('t_InventoryTypes')
    //         ->where('Type', $codeDetailId)
    //         ->value('Id');

    //     if (!$inventoryTypeId) {
    //         $inventoryTypeId = DB::table('t_InventoryTypes')->insertGetId([
    //             'Type' => $codeDetailId,
    //             'Status' => 1,
    //             'CreatedBy' => 1,
    //             'ModifiedBy' => 1,
    //             'CreatedOn' => now(),
    //             'ModifiedOn' => now(),
    //         ]);
    //     }

    //     return $inventoryTypeId;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed for data fix
    }
};
