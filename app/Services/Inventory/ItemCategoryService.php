<?php

namespace App\Services\Inventory;

use App\Models\Inventory\ItemCategories;
use App\Models\Core\Approval\CodeDetail;
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

                    // Default to Active if no status provided
                    if (empty($data['Status'])) {
                        $data['Status'] = CodeDetail::where('CodeID', 'CategoryStatus')
                            ->where('Description', 'Active')
                            ->value('Id');
                    }

                    return ItemCategories::create($data);
                }, 5);

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
                    usleep(100000);
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
        return str_contains(strtolower($e->getMessage()), 't_itemcategories_categorycode_unique');
    }

    public function update(ItemCategories $category, array $data): ItemCategories
    {
        DB::transaction(function () use ($category, $data) {
            $category->update($data);

            if (isset($data['Status'])) {
                $inactiveId = CodeDetail::where('CodeID', 'CategoryStatus')
                    ->where('Description', 'Inactive')
                    ->value('Id');

                $activeId = CodeDetail::where('CodeID', 'CategoryStatus')
                    ->where('Description', 'Active')
                    ->value('Id');

                if ($data['Status'] == $inactiveId || $data['Status'] == $activeId) {
                    $this->cascadeStatus($category, $data['Status']);
                }
            }
        });

        activity()
            ->causedBy(auth()->user())
            ->performedOn($category)
            ->event('update')
            ->log('Updated Item Category ' . $category->CategoryCode);

        return $category;
    }

    protected function cascadeStatus(ItemCategories $category, int $statusId): void
    {
        $category->loadMissing(['children', 'items']);

        // Cascade to child categories
        foreach ($category->children ?? [] as $child) {
            $child->update([
                'Status' => $statusId,
                'ModifiedBy' => auth()->id(),
                'ModifiedOn' => now()
            ]);

            $this->cascadeStatus($child, $statusId);
        }

        // Cascade to items under this category
        foreach ($category->items ?? [] as $item) {
            $item->update([
                'Status' => $statusId,
                'ModifiedBy' => auth()->id(),
                'ModifiedOn' => now()
            ]);
        }
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
