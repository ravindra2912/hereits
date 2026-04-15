<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use \App\Models\BusinessSetting;

trait GoogleDrive
{
    private $clientId;
    private $clientSecret;
    private $refreshToken;
    private $folderName;

    public function initGoogleDrive()
    {
        $settings = BusinessSetting::where('business_id', getBusinessId())
            ->first(['google_drive_client_id', 'google_drive_client_secret', 'google_drive_refresh_token']);

        $this->clientId = $settings->google_drive_client_id;
        $this->clientSecret = $settings->google_drive_client_secret;
        $this->refreshToken = $settings->google_drive_refresh_token;
        $this->folderName = env('GOOGLE_DRIVE_FOLDER_NAME', config('app.name', 'Shoper_Uploads'));
    }

    /**
     * Get Access Token using Refresh Token
     */
    private function getAccessToken()
    {
        $response = Http::post('https://oauth2.googleapis.com/token', [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $this->refreshToken,
            'grant_type' => 'refresh_token',
        ]);

        if ($response->successful()) {
            return $response->json('access_token');
        }

        Log::error('Google Drive: Failed to get access token', ['response' => $response->json()]);
        return null;
    }

    /**
     * Check connection to Google Drive and ensure folder exists by name
     */
    public function checkConnection()
    {
        $this->initGoogleDrive();
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            return ['success' => false, 'message' => 'Failed to obtain access token'];
        }

        // Check user info first to verify API access
        $aboutResponse = Http::withToken($accessToken)->get('https://www.googleapis.com/drive/v3/about?fields=user');
        if (!$aboutResponse->successful()) {
            return ['success' => false, 'message' => 'API access failed: ' . $aboutResponse->json('error.message')];
        }



        // Search for folder by name
        $searchResponse = Http::withToken($accessToken)->get("https://www.googleapis.com/drive/v3/files", [
            'q' => "name = '{$this->folderName}' and mimeType = 'application/vnd.google-apps.folder' and trashed = false",
            'fields' => 'files(id, name)',
            'pageSize' => 1
        ]);

        if ($searchResponse->successful() && count($searchResponse->json('files')) > 0) {
            $folderId = $searchResponse->json('files.0.id');
            return [
                'success' => true,
                'message' => "Connection successful. Folder '{$this->folderName}' found.",
                'folder_id' => $folderId,
                'user' => $aboutResponse->json('user')
            ];
        }

        // Folder not found, create it
        $createResponse = $this->createFolder($this->folderName);
        if ($createResponse['success']) {
            return [
                'success' => true,
                'message' => "Connection successful. Folder '{$this->folderName}' was missing and has been created.",
                'folder_id' => $createResponse['folder_id'],
                'user' => $aboutResponse->json('user')
            ];
        }

        return ['success' => false, 'message' => 'Connection successful but folder verification/creation failed: ' . $createResponse['message']];
    }

    /**
     * Create a folder in Google Drive
     * 
     * @param string $name
     * @return array
     */
    public function createFolder($name)
    {
        $this->initGoogleDrive();
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            return ['success' => false, 'message' => 'Authentication failed'];
        }

        $metadata = [
            'name' => $name,
            'mimeType' => 'application/vnd.google-apps.folder',
        ];

        $response = Http::withToken($accessToken)->post('https://www.googleapis.com/drive/v3/files', $metadata);

        if ($response->successful()) {
            return ['success' => true, 'folder_id' => $response->json('id')];
        }

        return ['success' => false, 'message' => $response->json('error.message', 'Folder creation failed')];
    }

    /**
     * Store file to Google Drive
     * 
     * @param mixed $file (UploadedFile or file content)
     * @param string $filename
     * @param string $mimeType
     * @return array
     */
    public function storeFile($file, $filename, $mimeType = 'application/octet-stream')
    {
        $this->initGoogleDrive();
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            return ['success' => false, 'message' => 'Authentication failed'];
        }

        // Ensure folder exists and get its ID
        $connection = $this->checkConnection();
        if (!$connection['success']) {
            return ['success' => false, 'message' => 'Folder verification failed: ' . $connection['message']];
        }
        $targetFolderId = $connection['folder_id'];

        $metadata = [
            'name' => $filename,
            'parents' => [$targetFolderId],
        ];

        $boundary = '-------314159265358979323846';
        $delimiter = "\r\n--" . $boundary . "\r\n";
        $closeDelimiter = "\r\n--" . $boundary . "--";

        $fileData = is_string($file) ? $file : file_get_contents($file->getRealPath());

        $body = $delimiter
            . 'Content-Type: application/json; charset=UTF-8' . "\r\n\r\n"
            . json_encode($metadata) . "\r\n"
            . $delimiter
            . 'Content-Type: ' . $mimeType . "\r\n\r\n"
            . $fileData
            . $closeDelimiter;

        $response = Http::withToken($accessToken)
            ->withHeaders(['Content-Type' => 'multipart/related; boundary=' . $boundary])
            ->withBody($body, 'multipart/related')
            ->post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart');

        if ($response->successful()) {
            $fileId = $response->json('id');
            // Make file public
            $this->makeFilePublic($fileId);

            return [
                'success' => true,
                'file_id' => $fileId,
                'file_url' => "https://drive.google.com/uc?id={$fileId}"
            ];
        }

        Log::error('Google Drive: Upload failed', ['response' => $response->json()]);
        return ['success' => false, 'message' => $response->json('error.message', 'Upload failed')];
    }

    /**
     * Remove file from Google Drive
     * 
     * @param string $fileId
     * @return array
     */
    public function removeFile($fileId)
    {
        $this->initGoogleDrive();
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            return ['success' => false, 'message' => 'Authentication failed'];
        }

        $response = Http::withToken($accessToken)->delete("https://www.googleapis.com/drive/v3/files/{$fileId}");

        if ($response->successful() || $response->status() == 404) {
            return ['success' => true];
        }

        Log::error('Google Drive: Delete failed', ['file_id' => $fileId, 'response' => $response->json()]);
        return ['success' => false, 'message' => $response->json('error.message', 'Delete failed')];
    }

    /**
     * Remove file from Google Drive using its URL
     * 
     * @param string $url
     * @return array
     */
    public function removeFileByUrl($url)
    {
        $fileId = $this->extractFileId($url);

        if (!$fileId) {
            return ['success' => false, 'message' => 'Could not extract File ID from URL'];
        }

        return $this->removeFile($fileId);
    }

    /**
     * Extract File ID from common Google Drive URL formats
     * 
     * @param string $url
     * @return string|null
     */
    private function extractFileId($url)
    {
        // Handle direct ID
        if (strlen($url) == 33 && !str_contains($url, '/')) {
            return $url;
        }

        // Patterns for common GD URL formats
        $patterns = [
            '/\/file\/d\/([a-zA-Z0-9_-]{33,})/', // /file/d/ID/view
            '/id=([a-zA-Z0-9_-]{33,})/',          // ?id=ID
            '/\/d\/([a-zA-Z0-9_-]{33,})/'           // /d/ID (thumbnail etc)
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Set file permission to public (anyone with link can view)
     * 
     * @param string $fileId
     * @return bool
     */
    private function makeFilePublic($fileId)
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) return false;

        $response = Http::withToken($accessToken)->post("https://www.googleapis.com/drive/v3/files/{$fileId}/permissions", [
            'role' => 'reader',
            'type' => 'anyone',
        ]);

        return $response->successful();
    }
}
