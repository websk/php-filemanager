<?php

namespace WebSK\FileManager;

use WebSK\Config\ConfWrapper;
use WebSK\Storage\StorageFactory;
use WebSK\Storage\StorageInterface;

/**
 * Class FileManager
 * @package WebSK\CRUD
 */
class FileManager
{
    const UPLOAD_FOLDER = 'uploads';

    /** @var string */
    protected $root_path;

    /** @var string */
    protected $url_path;

    /** @var array */
    protected $allowed_extensions = [];

    /** @var array */
    protected $allowed_types = [];

    /** @var StorageInterface */
    protected $storage;

    /**
     * FileManager constructor.
     * @param string $storage_name
     */
    public function __construct(string $storage_name)
    {
        $storage_config = ConfWrapper::value('storages.' . $storage_name, []);

        if (!isset($storage_config)) {
            throw new \Exception('A config must be specified');
        }

        $this->root_path = $storage_config['root_path'] ?? '';
        $this->url_path = $storage_config['url_path'] ?? '';
        $this->allowed_extensions = $storage_config['allowed_extensions'] ?? '';
        $this->allowed_types = $storage_config['allowed_types'] ?? '';

        $this->storage = StorageFactory::factory($storage_config);
    }

    /**
     * @param string $file_path
     * @return bool
     */
    public function deleteFile(string $file_path)
    {
        if (!$this->storage->has($file_path)) {
            return false;
        }

        return $this->storage->delete($file_path);
    }

    /**
     * @param $file
     * @param string $target_folder
     * @param string|null $error
     * @return string
     * @throws \Exception
     */
    public function storeUploadedFile($file, string $target_folder, &$error = null)
    {
        $file_name = $file['name'];
        $tmp_file_path = $file['tmp_name'];

        if (!\is_uploaded_file($tmp_file_path)) {
            $error = 'Не удалось загрузить файл';
            return '';
        }

        $allowed_extensions = $this->getAllowedExtensions();
        $allowed_types = $this->getAllowedTypes();

        $file_info = new \SplFileInfo($file_name);

        if (!in_array($file["type"], $allowed_types)) {
            $error = 'Тип ' . $file['type'] . ' загружаемого файла ' . $file_name . ' не поддерживается ';
            return '';
        }

        $file_extension = mb_strtolower($file_info->getExtension());
        if (!in_array($file_extension, $allowed_extensions)) {
            $error = 'Формат ' . $file_extension . ' загружаемого файла ' . $file_name . ' не поддерживается ';
            return '';
        }

        if ($file["error"] > 0) {
            $error = 'Не удалось загрузить файл';
            return '';
        }

        $upload_file_path = self::UPLOAD_FOLDER . '/' . $file_name;

        $stream = fopen($tmp_file_path, 'r+');
        $response = $this->storage->writeStream(
            $upload_file_path,
            $stream
        );
        if (is_resource($stream)) {
            fclose($stream);
        }

        return $this->storeFile($file_name, $upload_file_path, $target_folder);
    }

    /**
     * @param string $file_name
     * @param string $upload_file_path
     * @param string $target_folder
     * @return string
     */
    public function storeFile(string $file_name, string $upload_file_path, string $target_folder)
    {
        if (!$this->storage->has($target_folder)) {
            if (!$this->storage->createDir($target_folder)) {
                throw new \Exception('Не удалось создать директорию: ' . $target_folder);
            }
        }

        $unique_filename = $this->getUniqueFileName($file_name);

        $destination_file_path = DIRECTORY_SEPARATOR . $target_folder . DIRECTORY_SEPARATOR . $unique_filename;

        if (!$this->storage->rename($upload_file_path, $destination_file_path)) {
            throw new \Exception('Не удалось переместить файл: ' . $upload_file_path . ' -> ' . $destination_file_path);
        }

        return $unique_filename;
    }

    /**
     * @param string $src_file_name
     * @return string
     */
    public function getUniqueFileName(string $src_file_name)
    {
        $ext = pathinfo($src_file_name, PATHINFO_EXTENSION);
        $file_name = str_replace(".", "", uniqid(md5($src_file_name), true)) . "." . $ext;

        return $file_name;
    }

    /**
     * @return StorageInterface
     */
    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    /**
     * @return string
     */
    public function getRootPath()
    {
        return $this->root_path;
    }

    /**
     * @return string
     */
    public function getUrlPath(): string
    {
        return $this->url_path;
    }

    /**
     * @param string $file_name
     * @return string
     */
    public function getFilePath(string $file_name)
    {
        return $this->getRootPath() . DIRECTORY_SEPARATOR . $file_name;
    }

    /**
     * @param string $file_name
     * @return string
     */
    public function getFileUrl(string $file_name)
    {
        return $this->getUrlPath() . DIRECTORY_SEPARATOR . $file_name;
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
