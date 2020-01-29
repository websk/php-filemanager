<?php

namespace WebSK\Storage;

/**
 * Interface StorageInterface
 * @package VitrinaTV\Storage
 */
interface StorageInterface
{
    /**
     * Create a file or update if exists
     * @param string $path
     * @param string $contents
     * @param array $meta_data_arr
     * @return bool
     */
    public function put(string $path, string $contents, array $meta_data_arr = []): bool;

    /**
     * @param string $path
     * @return bool
     */
    public function has(string $path): bool;

    /**
     * @param string $path
     * @return bool
     */
    public function delete(string $path): bool;
}
