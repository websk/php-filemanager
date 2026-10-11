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
    /** @var array<string, mixed> */
    protected array $config = [
        'host'       => '',
        'port'       => 22,
        'username'   => '',
        'password'   => null,
        'privateKey' => null,
        'passphrase' => null,
        'root' => '',
        'timeout' => 10,
        'maxTries' => 4,
        'hostFingerprint' => null,
        'useAgent' => false,
    ];

    /**
     * @param array<string, mixed> $config
     */
    public function getAdapter(array $config): FilesystemAdapter
    {
        if (!isset($config['host']) || !is_string($config['host']) || trim($config['host']) === '') {
            throw new \InvalidArgumentException('The SFTP connector requires a non-empty host configuration.');
        }

        if (!isset($config['username']) || !is_string($config['username']) || trim($config['username']) === '') {
            throw new \InvalidArgumentException('The SFTP connector requires a non-empty username configuration.');
        }

        $host = trim($config['host']);
        $username = trim($config['username']);
        $config = array_replace_recursive($this->config, $config);
        $config['host'] = $host;
        $config['username'] = $username;

        foreach (['password', 'privateKey', 'passphrase'] as $nullable_key) {
            if ($config[$nullable_key] === '') {
                $config[$nullable_key] = null;
            }
        }

        if (!is_string($config['root'])) {
            throw new \InvalidArgumentException('The SFTP root configuration must be a string.');
        }

        $connection_provider = SftpConnectionProvider::fromArray($config);

        return new SftpAdapter($connection_provider, $config['root']);
    }
}
