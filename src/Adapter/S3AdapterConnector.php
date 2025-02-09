<?php

namespace WebSK\FileManager\Adapter;

use Aws\S3\S3Client;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\AwsS3v3\AwsS3V3Adapter;

/**
 * Class S3AdapterConnector
 * @package WebSK\Adapter
 */
class S3AdapterConnector implements AdapterConnectorInterface
{
    protected array $config = [
        'url' => 'http://localhost:9000',
        'access_key' => '',
        'secret_key' => '',
        'connect_timeout' => 2,
        'timeout' => 5,
        'region' => 'eu-central-1',
        'retries' => 1
    ];

    /**
     * @param array $config
     * @return FilesystemAdapter
     */
    public function getAdapter(array $config): FilesystemAdapter
    {
        if (!array_key_exists('bucket', $config)) {
            throw new \Exception('The awss3 connector requires bucket configuration.');
        }

        $config = array_replace_recursive($this->config, $config);

        // https://github.com/aws/aws-sdk-php/blob/master/docs/guide/configuration.rst
        $s3_client = new S3Client([
            'version' => 'latest',
            'region'  => $config['region'],
            'endpoint' => $config['url'],
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key'    => $config['access_key'],
                'secret' => $config['secret_key'],
            ],
            'http' => [
                'timeout' => $config['timeout'], // A float describing the timeout of the request(dns resolve + connect + upload/download) in seconds.
                'connect_timeout' => $config['connect_timeout'], // A float describing the number of seconds to wait while trying to connect to a server.
            ],
            'retries' => $config['retries'], // Configures the maximum number of allowed retries for a client
        ]);

        return new AwsS3V3Adapter($s3_client, $config['bucket']);
    }
}
