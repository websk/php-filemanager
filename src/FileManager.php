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

    /** @var list<string> */
    protected array $allowed_extensions = [];

    /** @var list<string> */
    protected array $allowed_types = [];

    protected Filesystem $storage;

    public function __construct(string $storage_name)
    {
        $storage_config = ConfWrapper::value(self::STORAGES_CONFIG_KEY . '.' . $storage_name, []);

        if (!is_array($storage_config) || $storage_config === []) {
            throw new \InvalidArgumentException('Storage configuration "' . $storage_name . '" was not found.');
        }

        $storage_config = self::normalizeConfig($storage_config);

        if (!isset($storage_config['adapter'])
            || !is_string($storage_config['adapter'])
            || trim($storage_config['adapter']) === ''
        ) {
            throw new \InvalidArgumentException(
                'Storage configuration "' . $storage_name . '" requires a non-empty adapter.'
            );
        }

        $this->root_path = self::readString($storage_config, 'root_path');
        $this->url_path = self::readString($storage_config, 'url_path');
        $this->allowed_extensions = self::readStringList($storage_config, 'allowed_extensions');
        $this->allowed_types = self::readStringList($storage_config, 'allowed_types');

        $this->storage = StorageFactory::factory($storage_config);
    }

    /**
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
     * @param mixed $file Expected keys: name, tmp_name, type, and error
     * @param string $target_folder
     * @param string $save_as
     * @param mixed $error Receives a string error message or null
     * @return string
     * @throws FilesystemException
     */
    public function storeUploadedFile(mixed $file, string $target_folder, string $save_as = '', mixed &$error = null): string
    {
        $error = null;

        if (!is_array($file)
            || !isset($file['name'], $file['tmp_name'], $file['type'], $file['error'])
            || !is_string($file['name'])
            || !is_string($file['tmp_name'])
            || !is_string($file['type'])
            || !is_int($file['error'])
        ) {
            $error = 'Invalid uploaded file data.';
            return '';
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = 'Failed to upload file.';
            return '';
        }

        $file_name = basename(str_replace('\\', '/', $file['name']));
        $tmp_file_path = $file['tmp_name'];

        if ($file_name === '' || !$this->isUploadedFile($tmp_file_path)) {
            $error = 'Failed to upload file.';
            return '';
        }

        $allowed_extensions = $this->getAllowedExtensions();
        $allowed_types = $this->getAllowedTypes();

        $file_info = new \SplFileInfo($file_name);

        if ($allowed_types && !in_array($file['type'], $allowed_types, true)) {
            $error = 'File type ' . $file['type'] . ' ' . $file_name . ' not supported';
            return '';
        }

        $file_extension = mb_strtolower($file_info->getExtension());
        if ($allowed_extensions && !in_array($file_extension, $allowed_extensions, true)) {
            $error = 'File extension ' . $file_extension . ' ' . $file_name . ' not supported';
            return '';
        }

        $target_folder = trim(str_replace('\\', '/', $target_folder), '/');

        if ($target_folder !== '' && !$this->storage->directoryExists($target_folder)) {
            $this->storage->createDirectory($target_folder);
        }

        if ($save_as !== '') {
            $file_name = basename(str_replace('\\', '/', $save_as)) . '.' . $file_extension;
        }

        $destination_file_path = ($target_folder === '' ? '' : $target_folder . '/') . $file_name;
        while ($this->storage->fileExists($destination_file_path)) {
            $file_name = $this->getUniqueFileName($file_name);
            $destination_file_path = ($target_folder === '' ? '' : $target_folder . '/') . $file_name;
        }

        $stream = @fopen($tmp_file_path, 'rb');
        if ($stream === false) {
            $error = 'Failed to open uploaded file.';
            return '';
        }

        try {
            $this->storage->writeStream($destination_file_path, $stream);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
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
        return rtrim($this->getUrlPath(), '/') . '/' . ltrim(str_replace('\\', '/', $file_name), '/');
    }

    /** @return list<string> */
    public function getAllowedExtensions(): array
    {
        return $this->allowed_extensions;
    }

    /** @return list<string> */
    public function getAllowedTypes(): array
    {
        return $this->allowed_types;
    }

    protected function isUploadedFile(string $file_path): bool
    {
        return is_uploaded_file($file_path);
    }

    /**
     * @param array<string, mixed> $config
     */
    private static function readString(array $config, string $key): string
    {
        $value = $config[$key] ?? '';
        if (!is_string($value)) {
            throw new \InvalidArgumentException('Storage configuration "' . $key . '" must be a string.');
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $config
     * @return list<string>
     */
    private static function readStringList(array $config, string $key): array
    {
        $value = $config[$key] ?? [];
        if (!is_array($value)) {
            throw new \InvalidArgumentException('Storage configuration "' . $key . '" must be an array.');
        }

        foreach ($value as $item) {
            if (!is_string($item)) {
                throw new \InvalidArgumentException(
                    'Storage configuration "' . $key . '" must contain only strings.'
                );
            }
        }

        return array_values($value);
    }

    /**
     * @param array<mixed, mixed> $config
     * @return array<string, mixed>
     */
    private static function normalizeConfig(array $config): array
    {
        $normalized = [];
        foreach ($config as $key => $value) {
            if (!is_string($key)) {
                throw new \InvalidArgumentException('Storage configuration keys must be strings.');
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }
}
