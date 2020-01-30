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
        $adapter = AdapterFactory::factory($config);

        switch ($config['adapter']) {
            case 'local':
                return new LocalStorage($adapter);
        }

        throw new \Exception("Unsupported adapter " . $config['adapter']);
    }
}
