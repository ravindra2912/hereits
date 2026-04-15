<?php

namespace App\Services;

use App\Traits\GoogleDrive;

class GoogleDriveService
{
    use GoogleDrive;

    protected static $instance;

    protected static function getInstance()
    {
        if (!static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public static function storeFile($file, $filename, $mimeType = 'application/octet-stream')
    {
        return static::getInstance()->traitStoreFile($file, $filename, $mimeType);
    }

    public static function removeFile($fileId)
    {
        return static::getInstance()->traitRemoveFile($fileId);
    }

    public static function removeFileByUrl($url)
    {
        return static::getInstance()->traitRemoveFileByUrl($url);
    }

    public static function checkConnection()
    {
        return static::getInstance()->traitCheckConnection();
    }

    // Proxy methods to match trait method names
    protected function traitStoreFile($file, $filename, $mimeType)
    {
        return $this->storeFile($file, $filename, $mimeType);
    }
    protected function traitRemoveFile($fileId)
    {
        return $this->removeFile($fileId);
    }
    protected function traitRemoveFileByUrl($url)
    {
        return $this->removeFileByUrl($url);
    }
    protected function traitCheckConnection()
    {
        return $this->checkConnection();
    }
}
