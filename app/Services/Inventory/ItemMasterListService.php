<?php

namespace App\Services\Inventory;

use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\ItemCategories;
use App\Models\Core\CodeDetail;
use App\Models\DMS\Image;
use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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

            // Handle image upload (as base64 in t_DMS_Images)
            if ($imageFile) {
                $image = $this->storeImage($imageFile);
                $item->ImageId = $image->ImageID;
            }

            // Save first to generate the ID before using it for document & code
            $item->save();

            // Assign or auto-generate item code
            if (!empty($data['ItemCode'])) {
                $item->ItemCode = $data['ItemCode'];
            } else {
                $item->ItemCode = 'ITM-' . str_pad($item->Id, 5, '0', STR_PAD_LEFT);
            }

            // Apply Inactive status if parent category is inactive
            $category = ItemCategories::find($item->Category);
            $inactiveId = CodeDetail::where('CodeID', 'ItemStatus')
                ->where('Description', 'Inactive')
                ->value('Id');

            if ($category && $category->status?->Description === 'Inactive') {
                $item->Status = $inactiveId;
            }

            $item->save();

            // Handle document using DMS attachment system (same pattern as FleetDriverService)
            if ($document) {
                // Remove old documents if any
                foreach ($item->documents as $doc) {
                    $doc->delete();
                }

                // Attach new document with permission control
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

    public function update(int $id, array $data, ?UploadedFile $imageFile = null, ?UploadedFile $document = null): ItemMasterList
{
    return DB::transaction(function () use ($id, $data, $imageFile, $document) {
        $item = ItemMasterList::findOrFail($id);

        $item->fill($data);
        $item->ModifiedBy = Auth::id();
        $item->ModifiedOn = now();

        // Handle category hierarchy update
        $item->Category = $data['SubCategory'] ?? $data['Category'] ?? $item->Category;

        // Handle image replacement
        if ($imageFile) {
            // Delete previous image if any
            if ($item->ImageId) {
                Image::destroy($item->ImageId);
            }
            $image = $this->storeImage($imageFile);
            $item->ImageId = $image->ImageID;
        }

        $item->save();

        // Handle document replacement
        if ($document) {
            // Remove old docs
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
        DB::transaction(function () use ($item) {
            $item->DeletedBy = Auth::id();
            $item->DeletedOn = Carbon::now();
            $item->save();

            // Delete linked image (if any)
            if ($item->ImageId) {
                Image::destroy($item->ImageId);
            }

            // Delete attached documents via DMS
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
