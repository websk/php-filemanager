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
     * @param array<string, mixed> $config
     */
    public function getAdapter(array $config): FilesystemAdapter;
}
