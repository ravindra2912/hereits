<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImageService
{
    /**
     * Get the configured storage disk driver ('local' or 'r2').
     *
     * @return string
     */
    protected function getDisk(): string
    {
        return config('filesystems.image_storage_disk', env('IMAGE_STORAGE_DISK', 'local'));
    }

    /**
     * Store/upload an image file.
     *
     * @param UploadedFile|null $imageObject The file to upload.
     * @param string $directory The target directory path.
     * @param int|string $width Optional width to resize the image.
     * @param int|string $height Optional height to resize the image.
     * @param string $convertTo Format to convert to (default: webp).
     * @return string The stored image path, or empty string on failure.
     */
    public function storeImage($imageObject, string $directory = "", $width = "", $height = "", string $convertTo = "webp"): string
    {
        if ($this->getDisk() === 'r2') {
            return $this->r2StoreImage($imageObject, $directory, $width, $height, $convertTo);
        }
        return $this->localStoreImage($imageObject, $directory, $width, $height, $convertTo);
    }

    /**
     * Retrieve the public URL for an image.
     *
     * @param string|null $url The image path or remote URL.
     * @param string $type The placeholder type (e.g. 'expert').
     * @return string The absolute URL of the image.
     */
    public function getImage(?string $url = null, string $type = ''): string
    {
        if ($this->getDisk() === 'r2') {
            return $this->r2GetImage($url, $type);
        }
        return $this->localGetImage($url, $type);
    }

    /**
     * Remove/delete an image from storage.
     *
     * @param string|null $imagePath The relative path of the image to remove.
     * @return bool True if successfully deleted, false otherwise.
     */
    public function removeImage(?string $imagePath): bool
    {
        if ($this->getDisk() === 'r2') {
            return $this->r2RemoveImage($imagePath);
        }
        return $this->localRemoveImage($imagePath);
    }


    // ====================================================
    //              Common Function
    // ====================================================

    /**
     * Extract YouTube ID from URL.
     *
     * @param string $url
     * @return string|null
     */
    private function getYoutubeId(string $url): ?string
    {
        preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|shorts/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
        return $match[1] ?? null;
    }

    /**
     * Get placeholder asset URL.
     *
     * @param string $type
     * @return string
     */
    private function getPlaceholder(string $type = ''): string
    {
        if ($type === 'expert') {
            return asset('assets/images/expert.webp');
        }
        return asset('assets/images/default.png');
    }


    // ====================================================
    //              Local Image Functions
    // ====================================================

    /**
     * Store/upload an image file to local storage.
     */
    public function localStoreImage($imageObject, string $directory = "", $width = "", $height = "", string $convertTo = "webp"): string
    {
        if (empty($imageObject) || !$imageObject instanceof UploadedFile) {
            return "";
        }

        // Sanitize directory name to prevent path traversal
        $directory = trim(preg_replace('/[^a-zA-Z0-9_\-\/]/', '', $directory), '/');

        $width = $width !== "" ? (int) $width : null;
        $height = $height !== "" ? (int) $height : null;

        if ($width !== null && $height !== null) {
            $imgname = time() . "_" . rand(11111, 99999) . '.' . $convertTo;
            $imageName = ($directory ? $directory . "/" : "") . $imgname;

            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            $driverClass = config('image.driver', GdDriver::class);
            $manager = new ImageManager(new $driverClass());
            $image = $manager->read($imageObject->path());
            $image->scale($width, $height); // resize

            $targetDir = public_path('/storage/' . $directory);
            if (!file_exists($targetDir)) {
                @mkdir($targetDir, 0755, true);
            }

            if (strtolower($convertTo) === 'webp') {
                $image->toWebp()->save(public_path('/storage/' . $imageName));
            } else {
                $image->save(public_path('/storage/' . $imageName));
            }
        } else {
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'pdf', 'csv', 'xlsx', 'xls', 'doc', 'docx'];
            $extension = strtolower($imageObject->getClientOriginalExtension());
            $guessedExt = strtolower($imageObject->guessExtension() ?? '');

            if (!in_array($extension, $allowedExtensions) || !in_array($guessedExt, $allowedExtensions)) {
                $extension = in_array($guessedExt, $allowedExtensions) ? $guessedExt : 'jpg';
            }

            $extension = preg_replace('/[^a-z0-9]/', '', $extension) ?: 'jpg';
            $imgname = time() . "_" . rand(11111, 99999) . '.' . $extension;
            $imageName = ($directory ? $directory . "/" : "") . $imgname;

            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            $fileContent = file_get_contents($imageObject->getRealPath());

            // Sanitize SVG to prevent stored XSS
            if ($extension === 'svg') {
                $fileContent = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $fileContent);
                $fileContent = preg_replace('/\bon[a-z]+\s*=\s*(["\']).*?\1/is', '', $fileContent);
                $fileContent = preg_replace('/\bhref\s*=\s*(["\'])\s*javascript:[^"\']*\1/is', '', $fileContent);
            }

            // Using local disk as defined in filesystems config (pointing to storage/app/public)
            Storage::disk('local')->put($imageName, $fileContent, 'public');
        }

        return $imageName;
    }

    /**
     * Retrieve the public URL for an image from local storage.
     */
    public function localGetImage(?string $url = null, string $type = ''): string
    {
        if (empty($url)) {
            return $this->getPlaceholder($type);
        }

        // Check if it's a YouTube URL
        if (str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be')) {
            $ytId = $this->getYoutubeId($url);
            if ($ytId) {
                return "https://img.youtube.com/vi/$ytId/hqdefault.jpg";
            }
        }

        if (str_contains($url, 'https://') || str_contains($url, 'http://')) {
            return $url;
        }

        $imagePath = "storage/" . $url;
        if (file_exists(public_path($imagePath)) || Storage::disk('public')->exists($url)) {
            return asset($imagePath);
        }

        return $this->getPlaceholder($type);
    }

    /**
     * Remove/delete an image from local storage.
     */
    public function localRemoveImage(?string $imagePath): bool
    {
        if (empty($imagePath)) {
            return false;
        }

        $deleted = false;
        if (Storage::disk('public')->exists($imagePath)) {
            $deleted = Storage::disk('public')->delete($imagePath);
        }

        if (Storage::disk('local')->exists($imagePath)) {
            $deleted = Storage::disk('local')->delete($imagePath) || $deleted;
        }

        $fullPath = public_path('storage/' . $imagePath);
        if (file_exists($fullPath)) {
            $deleted = @unlink($fullPath) || $deleted;
        }

        return $deleted;
    }


    // ====================================================
    //          Cloudflare R2 Image Functions
    // ====================================================

    /**
     * Store/upload an image file to Cloudflare R2 storage.
     */
    public function r2StoreImage($imageObject, string $directory = "", $width = "", $height = "", string $convertTo = "webp"): string
    {
        if (empty($imageObject) || !$imageObject instanceof UploadedFile) {
            return "";
        }

        // Sanitize directory name to prevent path traversal
        $directory = trim(preg_replace('/[^a-zA-Z0-9_\-\/]/', '', $directory), '/');

        $width = $width !== "" ? (int) $width : null;
        $height = $height !== "" ? (int) $height : null;

        if ($width !== null && $height !== null) {
            $imgname = time() . "_" . rand(11111, 99999) . '.' . $convertTo;
            $imageName = ($directory ? $directory . "/" : "") . $imgname;

            $driverClass = config('image.driver', GdDriver::class);
            $manager = new ImageManager(new $driverClass());
            $image = $manager->read($imageObject->path());
            $image->scale($width, $height); // resize

            if (strtolower($convertTo) === 'webp') {
                $encoded = $image->toWebp();
            } else {
                $encoded = $image->encode(); // default encode
            }

            Storage::disk('r2')->put($imageName, (string) $encoded, 'public');
        } else {
            $imgname = time() . "_" . rand(11111, 99999) . '.' . $imageObject->getClientOriginalExtension();
            $imageName = ($directory ? $directory . "/" : "") . $imgname;

            Storage::disk('r2')->put($imageName, file_get_contents($imageObject->getRealPath()), 'public');
        }

        return $imageName;
    }

    /**
     * Retrieve the public URL for an image from Cloudflare R2 storage.
     */
    public function r2GetImage(?string $url = null, string $type = ''): string
    {
        if (empty($url)) {
            return $this->getPlaceholder($type);
        }

        // Check if it's a YouTube URL
        if (str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be')) {
            $ytId = $this->getYoutubeId($url);
            if ($ytId) {
                return "https://img.youtube.com/vi/$ytId/hqdefault.jpg";
            }
        }

        if (str_contains($url, 'https://') || str_contains($url, 'http://')) {
            return $url;
        }

        return Storage::disk('r2')->url($url);
    }

    /**
     * Remove/delete an image from Cloudflare R2 storage.
     */
    public function r2RemoveImage(?string $imagePath): bool
    {
        if (empty($imagePath)) {
            return false;
        }

        try {
            if (Storage::disk('r2')->exists($imagePath)) {
                return Storage::disk('r2')->delete($imagePath);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to remove image from R2: ' . $e->getMessage());
        }

        return false;
    }
}
