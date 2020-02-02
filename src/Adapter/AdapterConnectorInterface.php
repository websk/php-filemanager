<?php

namespace WebSK\FileManager\Adapter;

use League\Flysystem\AdapterInterface;

/**
 * Interface AdapterConnectorInterface
 * @package WebSK\Adapter
 */
interface AdapterConnectorInterface
{
    /**
     * @param array $config
     * @return AdapterInterface
     */
    public function getAdapter(array $config): AdapterInterface;
}