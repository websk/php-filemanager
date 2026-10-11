<?php

declare(strict_types=1);

namespace WebSK\FileManager\Tests;

use League\Flysystem\Filesystem;
use PHPUnit\Framework\TestCase;
use WebSK\FileManager\Adapter\S3AdapterConnector;
use WebSK\FileManager\Adapter\SftpAdapterConnector;

final class RemoteAdapterIntegrationTest extends TestCase
{
    public function testS3RoundTripWhenConfigured(): void
    {
        $bucket = getenv('S3_TEST_BUCKET');
        if ($bucket === false || $bucket === '') {
            self::markTestSkipped('S3_TEST_BUCKET is not configured.');
        }

        $config = [
            'bucket' => $bucket,
            'region' => getenv('S3_TEST_REGION') ?: 'eu-central-1',
            'url' => getenv('S3_TEST_ENDPOINT') ?: null,
            'access_key' => getenv('S3_TEST_ACCESS_KEY') ?: null,
            'secret_key' => getenv('S3_TEST_SECRET_KEY') ?: null,
            'use_path_style_endpoint' => (bool) getenv('S3_TEST_ENDPOINT'),
        ];
        $filesystem = new Filesystem((new S3AdapterConnector())->getAdapter($config));

        $this->assertRoundTrip($filesystem, 'php-filemanager-s3-' . bin2hex(random_bytes(8)) . '.txt');
    }

    public function testSftpRoundTripWhenConfigured(): void
    {
        $host = getenv('SFTP_TEST_HOST');
        $username = getenv('SFTP_TEST_USERNAME');
        if ($host === false || $host === '' || $username === false || $username === '') {
            self::markTestSkipped('SFTP_TEST_HOST and SFTP_TEST_USERNAME are not configured.');
        }

        $filesystem = new Filesystem((new SftpAdapterConnector())->getAdapter([
            'host' => $host,
            'username' => $username,
            'password' => getenv('SFTP_TEST_PASSWORD') ?: null,
            'privateKey' => getenv('SFTP_TEST_PRIVATE_KEY') ?: null,
            'passphrase' => getenv('SFTP_TEST_PASSPHRASE') ?: null,
            'port' => (int) (getenv('SFTP_TEST_PORT') ?: 22),
            'root' => getenv('SFTP_TEST_ROOT') ?: '',
        ]));

        $this->assertRoundTrip($filesystem, 'php-filemanager-sftp-' . bin2hex(random_bytes(8)) . '.txt');
    }

    private function assertRoundTrip(Filesystem $filesystem, string $path): void
    {
        $written = false;
        try {
            $filesystem->write($path, 'php-filemanager');
            $written = true;
            self::assertSame('php-filemanager', $filesystem->read($path));
        } finally {
            if ($written && $filesystem->fileExists($path)) {
                $filesystem->delete($path);
            }
        }
    }
}
