<?php

declare(strict_types=1);

namespace WebSK\FileManager\Tests\Adapter;

use League\Flysystem\Filesystem;
use PHPUnit\Framework\TestCase;
use WebSK\FileManager\Adapter\LocalAdapterConnector;

final class LocalAdapterIntegrationTest extends TestCase
{
    public function testCreatesReadsAndDeletesFilesAndDirectories(): void
    {
        $root = sys_get_temp_dir() . '/php-filemanager-local-' . bin2hex(random_bytes(8));
        $filesystem = new Filesystem((new LocalAdapterConnector())->getAdapter(['root_path' => $root]));

        try {
            $filesystem->createDirectory('nested');
            $filesystem->createDirectory('nested');
            $filesystem->write('nested/file.txt', 'contents');

            self::assertTrue($filesystem->directoryExists('nested'));
            self::assertSame('contents', $filesystem->read('nested/file.txt'));

            $filesystem->delete('nested/file.txt');
            self::assertFalse($filesystem->fileExists('nested/file.txt'));
        } finally {
            @unlink($root . '/nested/file.txt');
            @rmdir($root . '/nested');
            @rmdir($root);
        }
    }
}
