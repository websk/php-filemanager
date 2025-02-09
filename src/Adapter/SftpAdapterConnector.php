<?php

namespace WebSK\FileManager\Adapter;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\PhpseclibV3\SftpAdapter;
use League\Flysystem\PhpseclibV3\SftpConnectionProvider;

/**
 * Class SftpAdapterConnector
 * @package WebSK\FileManager\Adapter
 */
class SftpAdapterConnector implements AdapterConnectorInterface
{
    protected array $config = [
        'host'       => 'localhost',
        'port'       => 22,
        'username'   => '',
        'password'   => '',
        'privateKey' => '',
        'passphrase' => '',
        'root' => '',
        'timeout' => 10
    ];

    /**
     * @param array $config
     * @return FilesystemAdapter
     */
    public function getAdapter(array $config): FilesystemAdapter
    {
        if (!array_key_exists('host', $config)) {
            throw new \Exception('The sftp connector requires host configuration.');
        }

        $config = array_replace_recursive($this->config, $config);

        $connection_provider = new SftpConnectionProvider(
            $config['host'],
            $config['username'],
            $config['password'],
            $config['privateKey'],
            $config['passphrase'],
            $config['port'],
            false,
            $config['timeout']
        );

        return new SftpAdapter($connection_provider, $config['root']);
    }
}
