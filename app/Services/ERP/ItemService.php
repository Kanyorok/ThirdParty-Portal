<?php
namespace App\Services\ERP;

use function Laravel\Prompts\select;

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

        return DB::table('t_Items')
//            ->join('t_ItemCategories')
            ->join('t_Item_categories','t_Items.category_id','=','t_Item_categories.id')
            ->where('t_Items.type',$type)
            ->select('t_Items.*','t_Item_categories.name')
            ->get();

    }

}
