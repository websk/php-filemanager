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
     * @param array $config
     * @return Filesystem
     * @throws \Exception
     */
    public static function factory(array $config)
    {
        $adapter = AdapterFactory::factory($config);

        switch ($config['adapter']) {
            case 'local':
            case 's3':
            case 'sftp':
                return new Filesystem($adapter);
        }

        throw new \Exception("Unsupported adapter " . $config['adapter']);
    }
}
