<?php

namespace WebSK\FileManager;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use WebSK\Config\ConfWrapper;
use WebSK\FileManager\Storage\StorageFactory;

/**
 * Class FileManager
 * @package WebSK\CRUD
 */
class FileManager
{
    const string STORAGES_CONFIG_KEY = 'storages';

    protected string $root_path;

    protected string $url_path;

    protected array $allowed_extensions = [];

    protected array $allowed_types = [];

    protected Filesystem $storage;

    /**
     * FileManager constructor.
     * @param string $storage_name
     */
    public function __construct(string $storage_name)
    {
        $storage_config = ConfWrapper::value(self::STORAGES_CONFIG_KEY . '.' . $storage_name, []);

        if (!isset($storage_config)) {
            throw new \Exception('A config must be specified');
        }

        $this->root_path = $storage_config['root_path'] ?? '';
        $this->url_path = $storage_config['url_path'] ?? '';
        $this->allowed_extensions = $storage_config['allowed_extensions'] ?? [];
        $this->allowed_types = $storage_config['allowed_types'] ?? [];

        $this->storage = StorageFactory::factory($storage_config);
    }

    /**r
     * @param string $file_path
     * @throws FilesystemException
     */
    public function deleteFileIfExist(string $file_path): void
    {
        if (!$this->storage->fileExists($file_path)) {
            return;
        }

        $this->storage->delete($file_path);
    }

    /**
     * @param $file
     * @param string $target_folder
     * @param string $save_as
     * @param $error
     * @return string
     * @throws FilesystemException
     */
    public function storeUploadedFile($file, string $target_folder, string $save_as = '', &$error = null): string
    {
        $file_name = $file['name'];
        $tmp_file_path = $file['tmp_name'];

        if (!\is_uploaded_file($tmp_file_path)) {
            $error = 'Failed to upload file.';
            return '';
        }

        $allowed_extensions = $this->getAllowedExtensions();
        $allowed_types = $this->getAllowedTypes();

        $file_info = new \SplFileInfo($file_name);

        if ($allowed_types && !in_array($file["type"], $allowed_types)) {
            $error = 'File type ' . $file['type'] . ' ' . $file_name . ' not supported';
            return '';
        }

        $file_extension = mb_strtolower($file_info->getExtension());
        if ($allowed_extensions && !in_array($file_extension, $allowed_extensions)) {
            $error = 'File extension ' . $file_extension . ' ' . $file_name . ' not supported';
            return '';
        }

        if ($file["error"] > 0) {
            $error = 'Failed to upload file.';
            return '';
        }

        if (!$this->storage->fileExists($target_folder)) {
            $this->storage->createDirectory($target_folder);
        }

        if ($save_as) {
            $file_name = $save_as . '.' . $file_extension;
        }

        $destination_file_path = DIRECTORY_SEPARATOR . $target_folder . DIRECTORY_SEPARATOR . $file_name;
        while ($this->storage->fileExists($destination_file_path)) {
            $file_name = $this->getUniqueFileName($file_name);
            $destination_file_path = DIRECTORY_SEPARATOR . $target_folder . DIRECTORY_SEPARATOR . $file_name;
        }

        $stream = fopen($tmp_file_path, 'r+');
        $this->storage->writeStream($destination_file_path, $stream);

        if (is_resource($stream)) {
            fclose($stream);
        }

        return $file_name;
    }

    /**
     * @param string $file_name
     * @return string
     */
    public function getUniqueFileName(string $file_name): string
    {
        return uniqid(md5($file_name), true) . "_" . $file_name;
    }

    /**
     * @return Filesystem
     */
    public function getStorage(): Filesystem
    {
        return $this->storage;
    }

    /**
     * @return string
     */
    public function getRootPath(): string
    {
        return $this->root_path;
    }

    /**
     * @return string
     */
    public function getUrlPath(): string
    {
        return str_replace('\\', '/', $this->url_path);
    }

    /**
     * @param string $file_name
     * @return string
     */
    public function getFileUrl(string $file_name): string
    {
        return $this->getUrlPath() . '/' . str_replace('\\', '/', $file_name);
    }

    /**
     * @return array
     */
    public function getAllowedExtensions(): array
    {
        return $this->allowed_extensions;
    }

    /**
     * @return array
     */
    public function getAllowedTypes(): array
    {
        return $this->allowed_types;
    }
}
