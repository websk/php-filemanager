<?php

declare(strict_types=1);

namespace WebSK\FileManager\Tests\Adapter;

use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\PhpseclibV3\SftpAdapter;
use PHPUnit\Framework\TestCase;
use WebSK\FileManager\Adapter\AdapterFactory;
use WebSK\FileManager\Adapter\LocalAdapterConnector;
use WebSK\FileManager\Adapter\S3AdapterConnector;
use WebSK\FileManager\Adapter\SftpAdapterConnector;

final class AdapterFactoryTest extends TestCase
{
    public function testRequiresAdapterConfiguration(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('An adapter must be specified.');

        AdapterFactory::createConnector([]);
    }

    public function testRejectsNonStringAdapter(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must be a string');

        AdapterFactory::createConnector(['adapter' => 1]);
    }

    public function testRejectsUnsupportedAdapter(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported adapter memory.');

        AdapterFactory::createConnector(['adapter' => 'memory']);
    }

    public function testCreatesConnectors(): void
    {
        self::assertInstanceOf(LocalAdapterConnector::class, AdapterFactory::createConnector(['adapter' => 'local']));
        self::assertInstanceOf(S3AdapterConnector::class, AdapterFactory::createConnector(['adapter' => 's3']));
        self::assertInstanceOf(SftpAdapterConnector::class, AdapterFactory::createConnector(['adapter' => 'sftp']));
    }

    public function testCreatesAdapters(): void
    {
        $root = sys_get_temp_dir() . '/php-filemanager-adapter-' . bin2hex(random_bytes(8));

        try {
            self::assertInstanceOf(
                LocalFilesystemAdapter::class,
                AdapterFactory::factory(['adapter' => 'local', 'root_path' => $root]),
            );
            self::assertInstanceOf(
                AwsS3V3Adapter::class,
                AdapterFactory::factory(['adapter' => 's3', 'bucket' => 'test-bucket']),
            );
            self::assertInstanceOf(
                SftpAdapter::class,
                AdapterFactory::factory([
                    'adapter' => 'sftp',
                    'host' => 'localhost',
                    'username' => 'test-user',
                ]),
            );
        } finally {
            @rmdir($root);
        }
    }
}
