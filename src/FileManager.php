<?php

namespace WebSK\FileManager;

use League\Flysystem\FilesystemInterface;
use WebSK\Config\ConfWrapper;
use WebSK\FileManager\Storage\StorageFactory;

/**
 * Class FileManager
 * @package WebSK\CRUD
 */
class FileManager
{
    const STORAGES_CONFIG_KEY = 'storages';

    /** @var string */
    protected $url_path;

    /** @var array */
    protected $allowed_extensions = [];

    /** @var array */
    protected $allowed_types = [];

    /** @var FilesystemInterface */
    protected $storage;

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

        $this->url_path = $storage_config['url_path'] ?? '';
        $this->allowed_extensions = $storage_config['allowed_extensions'] ?? '';
        $this->allowed_types = $storage_config['allowed_types'] ?? '';

        $this->storage = StorageFactory::factory($storage_config);
    }

    /**
     * @param string $file_path
     * @return bool
     */
    public function deleteFileIfExist(string $file_path)
    {
        if (!$this->storage->has($file_path)) {
            return false;
        }

        return $this->storage->delete($file_path);
    }

    /**
     * @param $file
     * @param string $target_folder
     * @param string $save_as
     * @param string|null $error
     * @return string
     * @throws \Exception
     */
    public function storeUploadedFile($file, string $target_folder, $save_as = '', &$error = null)
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

        if (!$this->storage->has($target_folder)) {
            if (!$this->storage->createDir($target_folder)) {
                throw new \Exception('Failed to create directory: ' . $target_folder);
            }
        }

        if ($save_as) {
            $file_name = $save_as . '.' . $file_extension;
        }

        $destination_file_path = DIRECTORY_SEPARATOR . $target_folder . DIRECTORY_SEPARATOR . $file_name;
        while ($this->storage->has($destination_file_path)) {
            $file_name = $this->getUniqueFileName($file_name);
            $destination_file_path = DIRECTORY_SEPARATOR . $target_folder . DIRECTORY_SEPARATOR . $file_name;
        }

        $stream = fopen($tmp_file_path, 'r+');
        if (!$this->storage->writeStream($destination_file_path, $stream)) {
            throw new \Exception('Failed to upload file in ' . $destination_file_path);
        }

        if (is_resource($stream)) {
            fclose($stream);
        }

        return $file_name;
    }

    /**
     * @param string $file_name
     * @return string
     */
    public function getUniqueFileName(string $file_name)
    {
        return uniqid(md5($file_name), true) . "_" . $file_name;
    }

    /**
     * @return FilesystemInterface
     */
    public function getStorage(): FilesystemInterface
    {
        return $this->storage;
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
    public function getFileUrl(string $file_name)
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
