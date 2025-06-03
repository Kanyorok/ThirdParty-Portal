<?php

namespace App\Services\Inventory;

use App\Models\Inventory\ItemMasterList;
use App\Models\DMS\Image;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ItemMasterListService
{
    public function create(array $data, ?UploadedFile $imageFile = null, ?UploadedFile $documentFile = null): ItemMasterList
    {
        return \DB::transaction(function () use ($data, $imageFile, $documentFile) {
            $item = new ItemMasterList();

            $item->fill($data);
            $item->CreatedBy = Auth::id();
            $item->CreatedOn = Carbon::now();
            $item->ModifiedBy = Auth::id();
            $item->ModifiedOn = Carbon::now();

            // Image upload
            if ($imageFile) {
                $image = $this->storeImage($imageFile);
                $item->ImageId = $image->ImageID;
            }

            // Document upload
            if ($documentFile) {
                $item->DocumentUpload = $documentFile->store('items/documents', 'public');
            }

            // Category or subcategory logic
            $item->Category = $data['SubCategory'] ?? $data['Category'];

            $item->save();

            // Generate ItemCode and update
            $item->ItemCode = 'ITM-' . str_pad($item->Id, 5, '0', STR_PAD_LEFT);
            $item->save();

            activity()
                ->causedBy(Auth::user())
                ->performedOn($item)
                ->event('created')
                ->log('Item created');

            return $item;
        });
    }

    public function update(ItemMasterList $item, array $data, ?UploadedFile $imageFile = null, ?UploadedFile $documentFile = null, bool $removeImage = false): ItemMasterList
    {
        return \DB::transaction(function () use ($item, $data, $imageFile, $documentFile, $removeImage) {
            $item->fill($data);
            $item->ModifiedBy = Auth::id();
            $item->ModifiedOn = Carbon::now();

            $item->Category = $data['SubCategory'] ?? $data['Category'];

            // Remove existing image
            if ($removeImage && $item->ImageId) {
                Image::destroy($item->ImageId);
                $item->ImageId = null;
            }

            // Replace image
            if ($imageFile) {
                if ($item->ImageId) {
                    Image::destroy($item->ImageId);
                }
                $image = $this->storeImage($imageFile);
                $item->ImageId = $image->ImageID;
            }

            // Replace document
            if ($documentFile) {
                if ($item->DocumentUpload) {
                    Storage::disk('public')->delete($item->DocumentUpload);
                }
                $item->DocumentUpload = $documentFile->store('items/documents', 'public');
            }

            $item->save();

            activity()
                ->causedBy(Auth::user())
                ->performedOn($item)
                ->event('updated')
                ->log('Item updated');

            return $item;
        });
    }

    public function delete(ItemMasterList $item): void
    {
        \DB::transaction(function () use ($item) {
            $item->DeletedBy = Auth::id();
            $item->DeletedOn = Carbon::now();
            $item->save();

            // Remove document file if exists
            if ($item->DocumentUpload) {
                Storage::disk('public')->delete($item->DocumentUpload);
            }

            // Remove image if exists
            if ($item->ImageId) {
                Image::destroy($item->ImageId);
            }

            $item->delete();

            activity()
                ->causedBy(Auth::user())
                ->performedOn($item)
                ->event('deleted')
                ->log('Item deleted');
        });
    }

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
