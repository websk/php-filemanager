<?php

namespace WebSK\Storage;

/**
 * Class StorageFactory
 * @package WebSK\Storage
 */
class StorageFactory
{
    /**
     * @param array $storage_config
     * @return LocalStorage
     * @throws \Exception
     */
    public static function factory(array $storage_config)
    {
        if (!isset($storage_config['adapter'])) {
            throw new \Exception('A adapter must be specified');
        }

        switch ($storage_config['adapter']) {
            case 'local':
                return new LocalStorage($storage_config['root_path']);
        }

        throw new \Exception("Unsupported adapter " . $storage_config['adapter']);
    }
}
