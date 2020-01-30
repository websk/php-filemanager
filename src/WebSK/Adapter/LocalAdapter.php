<?php

namespace WebSK\Adapter;

use League\Flysystem\Adapter\Local;
use League\Flysystem\AdapterInterface;

/**
 * Class LocalAdapter
 * @package WebSK\Adapter
 */
class LocalAdapter implements AdapterConnectionInterface
{
    /**
     * @param array $config
     * @return AdapterInterface
     */
    public function connect(array $config): AdapterInterface
    {
        if (!isset($config['root_path'])) {
            throw new \Exception('A root path must be specified');
        }

        $path = $config['root_path'];
        $write_flags = $config['write_flags'] ?? LOCK_EX;
        $link_handling = $config['link_handling'] ?? Local::DISALLOW_LINKS;
        $permissions = $config['permissions'] ?? [];

        return new Local($path, $write_flags, $link_handling, $permissions);
    }
}
