<?php

namespace App\Services\Inventory;

use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\ItemCategories;
use App\Models\Core\Approval\CodeDetail;
use App\Models\DMS\Image;
use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Http\UploadedFile;

class ItemMasterListService
{
    /**
     * Create a new Item Master entry with proper handling for category, image, and document.
     */
    public function create(array $data, ?UploadedFile $imageFile = null, ?UploadedFile $document = null): ItemMasterList
    {
        return DB::transaction(function () use ($data, $imageFile, $document) {
            $item = new ItemMasterList($data);
            $item->CreatedBy = Auth::id();
            $item->CreatedOn = now();
            $item->ModifiedBy = Auth::id();
            $item->ModifiedOn = now();

            // Handle category hierarchy
            $item->Category = $data['SubCategory'] ?? $data['Category'] ?? null;

            // Handle image upload
            if ($imageFile) {
                $image = $this->storeImage($imageFile);
                $item->ImageId = $image->ImageID;
            }

            // Save to generate ID
            $item->save();

            // Generate or assign Item Code
            $item->ItemCode = $data['ItemCode'] ?? 'ITM-' . str_pad($item->Id, 5, '0', STR_PAD_LEFT);

            // Set inactive if parent category is inactive
            $category = ItemCategories::find($item->Category);
            $inactiveId = CodeDetail::where('CodeID', 'ItemStatus')
                ->where('Description', 'Inactive')
                ->value('Id');

            if ($category && $category->status?->Description === 'Inactive') {
                $item->Status = $inactiveId;
            }

            $item->save();

            // Handle document attachment (if any)
            if ($document) {
                foreach ($item->documents as $doc) {
                    $doc->delete();
                }

                $item->newDocument(
                    ModulesEnum::Inventory,
                    $document,
                    [PermissionEnum::MasterListView->value],
                    Auth::user()
                );
            }

            activity()
                ->causedBy(Auth::user())
                ->performedOn($item)
                ->event('created')
                ->log('Item created');

            return $item;
        });
    }

    /**
     * Update an existing Item Master entry with category, image, and documents handling.
     */
    public function update(int $id, array $data, ?UploadedFile $imageFile = null, UploadedFile|array|null $document = null): ItemMasterList
    {
        return DB::transaction(function () use ($id, $data, $imageFile, $document) {
            $item = ItemMasterList::findOrFail($id);

            $item->fill($data);
            $item->ModifiedBy = Auth::id();
            $item->ModifiedOn = now();

            // Handle category hierarchy update
            $item->Category = $data['SubCategory'] ?? $data['Category'] ?? $item->Category;

            // Handle image removal
            if (!empty($data['remove_image']) && $data['remove_image'] == '1') {
                if ($item->ImageId) {
                    Image::destroy($item->ImageId);
                }
                $item->ImageId = null;
            }

            // Handle image replacement
            if ($imageFile) {
                if ($item->ImageId) {
                    Image::destroy($item->ImageId);
                }
                $image = $this->storeImage($imageFile);
                $item->ImageId = $image->ImageID;
            }

            $item->save();

            // Handle document replacement (single or multiple)
            if ($document) {
                foreach ($item->documents as $doc) {
                    $doc->delete();
                }

                $documents = is_array($document) ? $document : [$document];
                foreach ($documents as $docFile) {
                    if ($docFile instanceof UploadedFile) {
                        $item->newDocument(
                            ModulesEnum::Inventory,
                            $docFile,
                            [PermissionEnum::MasterListView->value],
                            Auth::user()
                        );
                    }
                }
            }

            activity()
                ->causedBy(Auth::user())
                ->performedOn($item)
                ->withProperties(['attributes' => $data])
                ->event('updated')
                ->log('Item updated');

            return $item;
        });
    }

    /**
     * Soft delete an item and remove its related media.
     */
    public function delete(ItemMasterList $item): void
    {
        $inactiveId = CodeDetail::where('CodeID', 'ItemStatus')
                ->where('Description', 'Inactive')
                ->value('Id');
        DB::transaction(function () use ($item, $inactiveId) {
            $item->Status = $inactiveId;
            $item->DeletedBy = Auth::id();
            $item->DeletedOn = Carbon::now();
            
            $item->save();

            // Delete linked image
            if ($item->ImageId) {
                Image::destroy($item->ImageId);
            }

            // Delete attached documents
            foreach ($item->documents as $doc) {
                $doc->delete();
            }

            $item->delete();

            activity()
                ->causedBy(Auth::user())
                ->performedOn($item)
                ->event('deleted')
                ->log('Item deleted');
        });
    }

    /**
     * Store an uploaded image in base64 format to DMS.
     */
    protected function storeImage(UploadedFile $file): Image
    {
        $imageContent = base64_encode(file_get_contents($file->getRealPath()));

        return Image::create([
            'Name' => $file->getClientOriginalName(),
            'Image' => $imageContent,
            'MIMEType' => $file->getMimeType(),
            'CreatedBy' => Auth::id(),
            'CreatedOn' => now(),
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);
    }
}
