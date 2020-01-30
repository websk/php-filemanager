<?php

namespace WebSK\Adapter;

use League\Flysystem\AdapterInterface;

/**
 * Interface AdapterConnectionInterface
 * @package WebSK\Adapter
 */
interface AdapterConnectionInterface
{
    /**
     * @param array $config
     * @return AdapterInterface
     */
    public function connect(array $config): AdapterInterface;
}