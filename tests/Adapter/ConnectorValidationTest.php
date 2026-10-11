<?php

declare(strict_types=1);

namespace WebSK\FileManager\Tests\Adapter;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use WebSK\FileManager\Adapter\LocalAdapterConnector;
use WebSK\FileManager\Adapter\S3AdapterConnector;
use WebSK\FileManager\Adapter\SftpAdapterConnector;

final class ConnectorValidationTest extends TestCase
{
    public function testLocalConnectorRequiresNonEmptyRootPath(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('non-empty root_path');

        (new LocalAdapterConnector())->getAdapter(['root_path' => '']);
    }

    public function testLocalConnectorValidatesFlags(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('flags must be integers');

        (new LocalAdapterConnector())->getAdapter([
            'root_path' => sys_get_temp_dir(),
            'write_flags' => 'LOCK_EX',
        ]);
    }

    /**
     * @param array<string, mixed> $config
     */
    #[DataProvider('invalidS3ConfigurationProvider')]
    public function testS3ConnectorValidatesConfiguration(array $config, string $message): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        (new S3AdapterConnector())->getAdapter($config);
    }

    /** @return iterable<string, array{array<string, mixed>, string}> */
    public static function invalidS3ConfigurationProvider(): iterable
    {
        yield 'bucket' => [[], 'non-empty bucket'];
        yield 'region' => [['bucket' => 'bucket', 'region' => ''], 'non-empty region'];
        yield 'url type' => [['bucket' => 'bucket', 'url' => []], 'string or null'];
        yield 'path style type' => [
            ['bucket' => 'bucket', 'use_path_style_endpoint' => 1],
            'must be a boolean',
        ];
        yield 'credential type' => [['bucket' => 'bucket', 'access_key' => []], 'strings or null'];
        yield 'credentials' => [
            ['bucket' => 'bucket', 'access_key' => 'access'],
            'must be specified together',
        ];
    }

    /**
     * @param array<string, mixed> $config
     */
    #[DataProvider('invalidSftpConfigurationProvider')]
    public function testSftpConnectorValidatesConfiguration(array $config, string $message): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        (new SftpAdapterConnector())->getAdapter($config);
    }

    /** @return iterable<string, array{array<string, mixed>, string}> */
    public static function invalidSftpConfigurationProvider(): iterable
    {
        yield 'host' => [[], 'non-empty host'];
        yield 'username' => [['host' => 'localhost'], 'non-empty username'];
        yield 'root' => [
            ['host' => 'localhost', 'username' => 'user', 'root' => []],
            'root configuration must be a string',
        ];
    }
}
