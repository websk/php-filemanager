<?php

namespace WebSK\FileManager\Adapter;

use League\Flysystem\Adapter\Local;
use League\Flysystem\AdapterInterface;

/**
 * Class LocalAdapter
 * @package WebSK\Adapter
 */
class LocalAdapterConnector implements AdapterConnectorInterface
{
    /**
     * @param array $config
     * @return AdapterInterface
     */
    public function getAdapter(array $config): AdapterInterface
    {
        if (!array_key_exists('root_path', $config)) {
            throw new \Exception('The local connector requires root_path configuration.');
        }

        $path = $config['root_path'];
        $write_flags = $config['write_flags'] ?? LOCK_EX;
        $link_handling = $config['link_handling'] ?? Local::DISALLOW_LINKS;
        $permissions = $config['permissions'] ?? [];

        return new Local($path, $write_flags, $link_handling, $permissions);
    }
}
