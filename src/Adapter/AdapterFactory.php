<?php

namespace WebSK\FileManager\Adapter;

use League\Flysystem\FilesystemAdapter;

/**
 * Class AdapterFactory
 * @package WebSK\Adapter
 */
class AdapterFactory
{
    /**
     * @param array $config
     * @return FilesystemAdapter
     * @throws \Exception
     */
    public static function factory(array $config): FilesystemAdapter
    {
        return self::createConnector($config)->getAdapter($config);
    }

    /**
     * @param array $config
     * @return AdapterConnectorInterface
     * @throws \Exception
     */
    public static function createConnector(array $config): AdapterConnectorInterface
    {
        if (!isset($config['adapter'])) {
            throw new \Exception('A adapter must be specified');
        }

        switch ($config['adapter']) {
            case 'local':
                return new LocalAdapterConnector();
            case 's3':
                return new S3AdapterConnector();
            case 'sftp':
                return new SftpAdapterConnector();
        }

        throw new \Exception("Unsupported adapter " . $config['adapter']);
    }
}
