<?php

namespace App\Services\ThirdParty;

use App\Exceptions\ErroredException;
use App\Models\Social;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SocialService
{
    public static function validateImage(string $imagePath): bool
    {
        // Check file existence
        if (!file_exists($imagePath)) {
            return false;
        }

        // Check file size (e.g., max 10MB)
        $maxFileSize = 10 * 1024 * 1024; // 10MB
        if (filesize($imagePath) > $maxFileSize) {
            return false;
        }

        // Check file type (basic image mime type check)
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $mimeType = mime_content_type($imagePath);

        return in_array($mimeType, $allowedTypes, true);
    }

    public function publish(Social $social): bool
    {
        return false;
    }

    /**
     * return Post Object
     *
     * @throws ErroredException
     */
    public function createPost(string $message, Carbon $publish_at, Collection $medias = null): object
    {
        if (!$publish_at->between(now()->addMinutes(10), now()->addDays(29)->endOfDay())) {
            throw new ErroredException('Scheduled time invalid, between 15 minutes and 29 days');
        }

        return $this->_createPost($message, $medias);
    }

    /**
     * @throws ErroredException
     */
    protected function _createPost(string $message, Collection $medias = null): object
    {
        throw new ErroredException('Not Implemented');
    }
}
