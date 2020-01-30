<?php

namespace WebSK\Adapter;

use League\Flysystem\AdapterInterface;

/**
 * Class AdapterFactory
 * @package WebSK\Adapter
 */
class AdapterFactory
{
    /**
     * @param array $config
     * @return AdapterInterface
     * @throws \Exception
     */
    public static function factory(array $config)
    {
        return self::createAdapter($config)->connect($config);
    }

    /**
     * @param array $config
     * @return AdapterConnectionInterface
     * @throws \Exception
     */
    public static function createAdapter(array $config)
    {
        if (!isset($config['adapter'])) {
            throw new \Exception('A adapter must be specified');
        }

        switch ($config['adapter']) {
            case 'local':
                return new LocalAdapter();
        }

        throw new \Exception("Unsupported adapter " . $config['adapter']);
    }
}
