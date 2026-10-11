<?php

namespace WebSK\FileManager\Storage;

use League\Flysystem\Filesystem;
use WebSK\FileManager\Adapter\AdapterFactory;

/**
 * Class StorageFactory
 * @package WebSK\Storage
 */
class StorageFactory
{

    /**
     * @param array<string, mixed> $config
     * @throws \Exception
     */
    public static function factory(array $config): Filesystem
    {
        $adapter = AdapterFactory::factory($config);

        return new Filesystem($adapter);
    }
}
