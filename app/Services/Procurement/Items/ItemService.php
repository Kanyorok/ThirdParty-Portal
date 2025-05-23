<?php
namespace App\Services\Procurement\Items;

use Illuminate\Support\Facades\DB;

class ItemService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
//
    }

    public static function getItemByType($type){
        // logger('Fetching items for type: ' . $type);

        return DB::table('t_Items')
            ->leftjoin('t_ItemCategories','t_Items.Category','=','t_ItemCategories.Id')
            ->where('t_Items.ItemType',$type)
            ->select('t_Items.Id','t_Items.ItemName','t_Items.ItemCode')
            ->get();
    }
    public static function getItemDetails($item){
        // logger('Fetching items details: ' . $item);

        return DB::table('t_Items')
            ->leftjoin('t_ItemCategories','t_Items.Category','=','t_ItemCategories.Id')
            ->where('t_Items.Id',$item)
            ->select([
                't_Items.ItemDescription',
                't_Items.UOM',
                DB::raw('0 as UnitPrice'),  // Properly casting the literal 0
                't_ItemCategories.Name'
            ])
            ->get();
    }



}
