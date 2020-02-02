<?php

namespace WebSK\FileManager\Adapter;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Sftp\SftpAdapter;

/**
 * Class SftpAdapterConnector
 * @package WebSK\FileManager\Adapter
 */
class SftpAdapterConnector implements AdapterConnectorInterface
{
    protected $config = [
        'host'       => 'localhost',
        'port'       => 22,
        'username'   => '',
        'password'   => '',
        'privateKey' => '',
        'passphrase' => '',
        'root' => '',
        'timeout' => 10,
        'directoryPerm' => 0755
    ];

    /**
     * @param array $config
     * @return AdapterInterface
     */
    public function getAdapter(array $config): AdapterInterface
    {
        if (!array_key_exists('host', $config)) {
            throw new \Exception('The sftp connector requires host configuration.');
        }

        $config = array_replace_recursive($this->config, $config);

        return new SftpAdapter($config);
    }
}
