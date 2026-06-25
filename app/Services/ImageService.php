<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
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
        return env('IMAGE_STORAGE_DISK', 'local');
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
     * @param string $url The image path or remote URL.
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

        $width = $width !== "" ? (int) $width : null;
        $height = $height !== "" ? (int) $height : null;

        if ($width !== null && $height !== null) {
            $imgname = time() . "_" . rand(11111, 99999) . '.' . $convertTo;
            $imageName = $directory . "/" . $imgname;

            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            $image = Image::read($imageObject->path());
            $image->scale($width, $height); // resize

            if (strtolower($convertTo) === 'webp') {
                $image->toWebp()->save(public_path('/storage/' . $imageName));
            } else {
                $image->save(public_path('/storage/' . $imageName));
            }
        } else {
            $imgname = time() . "_" . rand(11111, 99999) . '.' . $imageObject->getClientOriginalExtension();
            $imageName = $directory . "/" . $imgname;

            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            // Using local disk as defined in filesystems config (pointing to storage/app/public)
            Storage::disk('local')->put($imageName, file_get_contents($imageObject), 'public');
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

        if (str_contains($url, 'https://') || str_contains($url, 'http://')) {
            // Check if it's a YouTube URL
            $ytId = $this->getYoutubeId($url);
            if ($ytId) {
                return "https://img.youtube.com/vi/$ytId/hqdefault.jpg";
            }

            return Cache::remember('valid_image_' . md5($url), 86400, function () use ($url, $type) {
                try {
                    $response = Http::timeout(2)->head($url);
                    if ($response->ok()) {
                        return $url;
                    }
                } catch (\Exception $e) {
                    Log::warning("Could not resolve image URL: " . $url . " - Error: " . $e->getMessage());
                }
                return $this->getPlaceholder($type);
            });
        }

        $imagePath = "storage/" . $url;
        if (file_exists(public_path($imagePath))) {
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

        // Using 'local' disk as configured to match root of storage_path('app/public')
        if (Storage::disk('local')->exists($imagePath)) {
            return Storage::disk('local')->delete($imagePath);
        }

        return false;
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

        $width = $width !== "" ? (int) $width : null;
        $height = $height !== "" ? (int) $height : null;

        if ($width !== null && $height !== null) {
            $imgname = time() . "_" . rand(11111, 99999) . '.' . $convertTo;
            $imageName = $directory . "/" . $imgname;

            $image = Image::read($imageObject->path());
            $image->scale($width, $height); // resize

            if (strtolower($convertTo) === 'webp') {
                $encoded = $image->toWebp();
            } else {
                $encoded = $image->encode(); // default encode
            }

            Storage::disk('r2')->put($imageName, (string) $encoded, 'public');
        } else {
            $imgname = time() . "_" . rand(11111, 99999) . '.' . $imageObject->getClientOriginalExtension();
            $imageName = $directory . "/" . $imgname;

            Storage::disk('r2')->put($imageName, file_get_contents($imageObject), 'public');
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

        if (str_contains($url, 'https://') || str_contains($url, 'http://')) {
            // Check if it's a YouTube URL
            $ytId = $this->getYoutubeId($url);
            if ($ytId) {
                return "https://img.youtube.com/vi/$ytId/hqdefault.jpg";
            }

            return Cache::remember('valid_image_' . md5($url), 86400, function () use ($url, $type) {
                try {
                    $response = Http::timeout(2)->head($url);
                    if ($response->ok()) {
                        return $url;
                    }
                } catch (\Exception $e) {
                    Log::warning("Could not resolve image URL: " . $url . " - Error: " . $e->getMessage());
                }
                return $this->getPlaceholder($type);
            });
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

        if (Storage::disk('r2')->exists($imagePath)) {
            return Storage::disk('r2')->delete($imagePath);
        }

        return false;
    }
}
