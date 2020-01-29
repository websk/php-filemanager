<?php

namespace WebSK\FileManager;


use WebSK\Config\ConfWrapper;

/**
 * Class FileManager
 * @package WebSK\CRUD
 */
class FileManager
{
    const DEFAULT_FILES_FOLDER = 'files';

    /** @var string */
    protected $files_root_path;

    /** @var string */
    protected $files_url_path;

    /** @var array */
    protected $allowed_extension = [];

    /** @var array */
    protected $allowed_types = [];

    /**
     * FileManager constructor.
     * @param string $files_root_path
     * @param string $files_url_path
     * @param array $allowed_extension
     * @param array $allowed_types
     */
    public function __construct(
        string $files_root_path = '',
        string $files_url_path = '',
        array $allowed_extension = [],
        array $allowed_types = []
    ) {
        if ($files_root_path) {
            $this->files_root_path = $files_root_path;
        } else {
            $this->files_root_path = ConfWrapper::value('files_root_path');
        }

        if ($files_url_path) {
            $this->files_url_path = $files_url_path;
        } else {
            $this->files_url_path = ConfWrapper::value('files_url_path', self::DEFAULT_FILES_FOLDER);
        }

        if ($allowed_extension) {
            $this->allowed_extension = $allowed_extension;
        } else {
            $this->allowed_extension = ConfWrapper::value('files_allowed_extension', []);
        }

        if ($allowed_types) {
            $this->allowed_types = $allowed_types;
        } else {
            $this->allowed_types = ConfWrapper::value('files_allowed_types', []);
        }
    }

    /**
     * @param string $file_name
     * @return bool
     */
    public function deleteFile(string $file_name)
    {
        $file_path = $this->getFilePath($file_name);

        return FileUtils::deleteFile($file_path);
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
        $tmp_file_name = $file['tmp_name'];

        if (!\is_uploaded_file($tmp_file_name)) {
            $error = 'Не удалось загрузить файл';
            return '';
        }

        $allowed_extensions = $this->getAllowedExtension();
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

        return $this->storeFile($file_name, $tmp_file_name, $target_folder);
    }

    /**
     * @param string $file_name
     * @param string $tmp_file_name
     * @param string $target_folder
     * @return string
     */
    public function storeFile(string $file_name, string $tmp_file_name, string $target_folder)
    {
        $file_path_in_file_components_arr = [];
        if ($target_folder != '') {
            $file_path_in_file_components_arr[] = $target_folder;
        }

        $unique_filename = $this->getUniqueFileName($file_name);
        $file_path_in_file_components_arr[] = $unique_filename;

        $new_name = implode(DIRECTORY_SEPARATOR, $file_path_in_file_components_arr);

        $new_path = $this->getFilePath($new_name);

        $destination_file_path = pathinfo($new_path, PATHINFO_DIRNAME);
        if (!is_dir($destination_file_path)) {
            if (!mkdir($destination_file_path, 0777, true)) {
                throw new \Exception('Не удалось создать директорию: ' . $destination_file_path);
            }
        }

        if (!rename($tmp_file_name, $new_path)) {
            throw new \Exception('Не удалось переместить файл: ' . $tmp_file_name . ' -> ' . $new_path);
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
     * @return string
     */
    public function getFilesRootPath()
    {
        return $this->files_root_path;
    }

    /**
     * @return string
     */
    public function getFilesUrlPath(): string
    {
        return $this->files_url_path;
    }

    /**
     * @param string $file_name
     * @return string
     */
    public function getFilePath(string $file_name)
    {
        return $this->getFilesRootPath() . DIRECTORY_SEPARATOR . $file_name;
    }

    /**
     * @param string $file_name
     * @return string
     */
    public function getFileUrl(string $file_name)
    {
        return $this->getFilesUrlPath() . DIRECTORY_SEPARATOR . $file_name;
    }

    /**
     * @return array
     */
    public function getAllowedExtension(): array
    {
        return $this->allowed_extension;
    }

    /**
     * @return array
     */
    public function getAllowedTypes(): array
    {
        return $this->allowed_types;
    }
}
