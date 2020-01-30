<?php

namespace WebSK\Storage;

use WebSK\Adapter\AdapterFactory;

/**
 * Class StorageFactory
 * @package WebSK\Storage
 */
class StorageFactory
{

    /**
     * @param array $config
     * @return StorageInterface
     * @throws \Exception
     */
    public static function factory(array $config)
    {
        if (!isset($config['adapter'])) {
            throw new \Exception('A adapter must be specified');
        }

        $adapter = AdapterFactory::factory($config);

        switch ($adapter) {
            case 'local':
                return new LocalStorage($adapter);
        }

        throw new \Exception("Unsupported adapter " . $config['adapter']);
    }
}
