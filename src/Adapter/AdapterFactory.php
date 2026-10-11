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
     * @param array<string, mixed> $config
     * @throws \Exception
     */
    public static function factory(array $config): FilesystemAdapter
    {
        return self::createConnector($config)->getAdapter($config);
    }

    /**
     * @param array<string, mixed> $config
     * @throws \Exception
     */
    public static function createConnector(array $config): AdapterConnectorInterface
    {
        if (!isset($config['adapter'])) {
            throw new \InvalidArgumentException('An adapter must be specified.');
        }

        if (!is_string($config['adapter'])) {
            throw new \InvalidArgumentException('The adapter configuration must be a string.');
        }

        switch ($config['adapter']) {
            case 'local':
                return new LocalAdapterConnector();
            case 's3':
                if (!class_exists(\League\Flysystem\AwsS3V3\AwsS3V3Adapter::class)) {
                    throw new \LogicException(
                        'The S3 adapter requires league/flysystem-aws-s3-v3. '
                        . 'Install it with composer require league/flysystem-aws-s3-v3:^3.35.'
                    );
                }

                return new S3AdapterConnector();
            case 'sftp':
                if (!class_exists(\League\Flysystem\PhpseclibV3\SftpAdapter::class)) {
                    throw new \LogicException(
                        'The SFTP adapter requires league/flysystem-sftp-v3. '
                        . 'Install it with composer require league/flysystem-sftp-v3:^3.36.'
                    );
                }

                return new SftpAdapterConnector();
        }

        throw new \InvalidArgumentException('Unsupported adapter ' . $config['adapter'] . '.');
    }
}
