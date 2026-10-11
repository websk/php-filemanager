<?php

namespace WebSK\FileManager\Adapter;

use Aws\S3\S3Client;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;

/**
 * Class S3AdapterConnector
 * @package WebSK\Adapter
 */
class S3AdapterConnector implements AdapterConnectorInterface
{
    /** @var array<string, mixed> */
    protected array $config = [
        'url' => null,
        'access_key' => null,
        'secret_key' => null,
        'connect_timeout' => 2,
        'timeout' => 5,
        'region' => 'eu-central-1',
        'retries' => 1,
        'use_path_style_endpoint' => false,
    ];

    /**
     * @param array<string, mixed> $config
     */
    public function getAdapter(array $config): FilesystemAdapter
    {
        if (!isset($config['bucket']) || !is_string($config['bucket']) || trim($config['bucket']) === '') {
            throw new \InvalidArgumentException('The S3 connector requires a non-empty bucket configuration.');
        }

        $bucket = trim($config['bucket']);
        $config = array_replace_recursive($this->config, $config);

        if (!is_string($config['region']) || trim($config['region']) === '') {
            throw new \InvalidArgumentException('The S3 connector requires a non-empty region configuration.');
        }
        $config['region'] = trim($config['region']);

        if ($config['url'] !== null && !is_string($config['url'])) {
            throw new \InvalidArgumentException('The S3 url configuration must be a string or null.');
        }

        if (!is_bool($config['use_path_style_endpoint'])) {
            throw new \InvalidArgumentException('The S3 use_path_style_endpoint configuration must be a boolean.');
        }

        $access_key = $config['access_key'];
        $secret_key = $config['secret_key'];
        if (($access_key !== null && !is_string($access_key))
            || ($secret_key !== null && !is_string($secret_key))
        ) {
            throw new \InvalidArgumentException('S3 credentials must be strings or null.');
        }

        $has_access_key = is_string($access_key) && $access_key !== '';
        $has_secret_key = is_string($secret_key) && $secret_key !== '';

        if ($has_access_key !== $has_secret_key) {
            throw new \InvalidArgumentException('S3 access_key and secret_key must be specified together.');
        }

        // https://github.com/aws/aws-sdk-php/blob/master/docs/guide/configuration.rst
        /** @var array<string, mixed> $client_config */
        $client_config = [
            'version' => 'latest',
            'region'  => $config['region'],
            'http' => [
                'timeout' => $config['timeout'], // A float describing the timeout of the request(dns resolve + connect + upload/download) in seconds.
                'connect_timeout' => $config['connect_timeout'], // A float describing the number of seconds to wait while trying to connect to a server.
            ],
            'retries' => $config['retries'], // Configures the maximum number of allowed retries for a client
        ];

        if (is_string($config['url']) && $config['url'] !== '') {
            $client_config['endpoint'] = $config['url'];
            $client_config['use_path_style_endpoint'] = (bool) $config['use_path_style_endpoint'];
        }

        if ($has_access_key && $has_secret_key) {
            $client_config['credentials'] = [
                'key' => $access_key,
                'secret' => $secret_key,
            ];
        }

        $s3_client = new S3Client($client_config);

        return new AwsS3V3Adapter($s3_client, $bucket);
    }
}
