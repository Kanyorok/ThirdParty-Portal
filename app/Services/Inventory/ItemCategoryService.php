<?php

namespace App\Services\Inventory;

use App\Models\Inventory\ItemCategories;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Exception;

class ItemCategoryService
{
    public function create(array $data): ItemCategories
    {
        $maxRetries = 3;
        $attempt = 0;

        do {
            try {
                $category = DB::transaction(function () use (&$data) {
                    $isSubcategory = !empty($data['ParentId']);
                    $data['CategoryCode'] = $this->generateCategoryCode($isSubcategory);
                    $data['CreatedBy'] = auth()->id();
                    $data['ModifiedBy'] = auth()->id();
                    $data['CreatedOn'] = now();
                    $data['ModifiedOn'] = now();

                    // If no status is selected, default to the first "Active" ID
                    if (empty($data['Status'])) {
                        $data['Status'] = CodeDetail::where('CodeID', 'CategoryStatus')
                            ->where('Description', 'Active')
                            ->value('ID');
                    }

                    return ItemCategories::create($data);
                }, 5);

                // Activity log after successful creation
                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($category)
                    ->event('create')
                    ->log('Created Item Category ' . $category->CategoryCode);

                return $category;

            } catch (QueryException $e) {
                if ($this->isDuplicateCategoryCodeError($e)) {
                    $attempt++;
                    if ($attempt >= $maxRetries) {
                        throw new Exception('Unable to generate unique CategoryCode after multiple attempts.');
                    }
                    usleep(100000); // wait before retrying
                } else {
                    throw $e;
                }
            }
        } while ($attempt < $maxRetries);
    }

    protected function generateCategoryCode(bool $isSubcategory): string
    {
        $prefix = $isSubcategory ? 'SUB' : 'CAT';

        $maxCode = DB::table('t_ItemCategories')
            ->whereRaw("LEFT(CategoryCode, 3) = ?", [$prefix])
            ->select(DB::raw("MAX(CAST(SUBSTRING(CategoryCode, 5, LEN(CategoryCode)) AS INT)) AS max_code"))
            ->lock('WITH (TABLOCKX, HOLDLOCK)')
            ->value('max_code');

        $nextNumber = $maxCode ? $maxCode + 1 : 1;

        return $prefix . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    protected function isDuplicateCategoryCodeError(QueryException $e): bool
    {
        return str_contains($e->getMessage(), 't_itemcategories_categorycode_unique');
    }

    public function destroy(ItemCategories $category): void
    {
        $category->DeletedBy = auth()->id();
        $category->save();
        $category->delete();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($category)
            ->event('delete')
            ->log('Deleted Item Category ' . $category->CategoryCode);
    }

}
