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
            ->join('t_ItemCategories','t_Items.CategoryId','=','t_ItemCategories.id')
            ->where('t_Items.type',$type)
            ->select('t_Items.id','t_Items.name','t_Items.UniqueCode')
            ->get();
    }
    public static function getItemDetails($item){
        // logger('Fetching items details: ' . $item);

        return DB::table('t_Items')
            ->join('t_ItemCategories','t_Items.CategoryId','=','t_ItemCategories.id')
            ->where('t_Items.id',$item)
            ->select('t_Items.Description','t_Items.UOM','t_Items.UnitPrice','t_Items.CategoryId')
            ->get();
    }



}
