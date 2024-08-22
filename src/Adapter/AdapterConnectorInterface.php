<?php

namespace WebSK\FileManager\Adapter;

use League\Flysystem\FilesystemAdapter;

/**
 * Interface AdapterConnectorInterface
 * @package WebSK\Adapter
 */
interface AdapterConnectorInterface
{
    /**
     * @param array $config
     * @return FilesystemAdapter
     */
    public function getAdapter(array $config): FilesystemAdapter;
}