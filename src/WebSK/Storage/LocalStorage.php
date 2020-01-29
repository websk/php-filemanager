<?php

namespace WebSK\Storage;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;

/**
 * Class LocalStorage
 * @package WebSK\Storage
 */
class LocalStorage implements StorageInterface
{
    /** @var FilesystemInterface */
    protected $filesystem;

    public function __construct(string $root_path)
    {
        $adapter = new Local($root_path);

        $this->filesystem = new Filesystem($adapter);
    }

    /**
     * Write a new file or exception if exists
     * @param string $path
     * @param string $contents
     * @param array $meta_data_arr
     * @return bool
     * @throws \League\Flysystem\FileExistsException
     */
    public function write(string $path, string $contents, array $meta_data_arr = [])
    {
        return $this->filesystem->write($path, $contents, $meta_data_arr);
    }

    /** @inheritdoc */
    public function put(string $path, string $contents, array $meta_data_arr = []): bool
    {
        return $this->filesystem->put($path, $contents, $meta_data_arr);
    }

    /**
     * @param string $path
     * @return bool
     */
    public function has(string $path): bool
    {
        return $this->filesystem->has($path);
    }

    /**
     * @param string $path
     * @return bool
     */
    public function delete(string $path): bool
    {
        return $this->filesystem->delete($path);
    }
}
