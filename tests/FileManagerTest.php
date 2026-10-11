<?php

declare(strict_types=1);

namespace WebSK\FileManager\Tests;

use PHPUnit\Framework\TestCase;
use WebSK\Config\ConfWrapper;
use WebSK\FileManager\FileManager;

final class FileManagerTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/php-filemanager-test-' . bin2hex(random_bytes(8));
        mkdir($this->root);
        ConfWrapper::setConfig([
            'storages' => [
                'files' => [
                    'adapter' => 'local',
                    'root_path' => $this->root,
                    'url_path' => '\\files\\',
                    'allowed_extensions' => ['txt'],
                    'allowed_types' => ['text/plain'],
                ],
            ],
        ]);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->root);
        ConfWrapper::setConfig([]);
    }

    public function testReadsStorageConfiguration(): void
    {
        $manager = new TestFileManager('files');

        self::assertSame($this->root, $manager->getRootPath());
        self::assertSame('/files/', $manager->getUrlPath());
        self::assertSame('/files/example.txt', $manager->getFileUrl('example.txt'));
        self::assertSame(['txt'], $manager->getAllowedExtensions());
        self::assertSame(['text/plain'], $manager->getAllowedTypes());
    }

    public function testRejectsMissingStorageConfiguration(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Storage configuration "missing" was not found.');

        new TestFileManager('missing');
    }

    public function testRejectsInvalidStorageConfiguration(): void
    {
        ConfWrapper::setConfig(['storages' => ['files' => ['adapter' => 'local', 'root_path' => []]]]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"root_path" must be a string');

        new TestFileManager('files');
    }

    public function testChecksUploadErrorBeforeUploadedFileStatus(): void
    {
        $manager = new TestFileManager('files');
        $error = null;

        $result = $manager->storeUploadedFile([
            'name' => 'example.txt',
            'tmp_name' => '/missing',
            'type' => 'text/plain',
            'error' => UPLOAD_ERR_INI_SIZE,
        ], '', '', $error);

        self::assertSame('', $result);
        self::assertSame('Failed to upload file.', $error);
        self::assertSame(0, $manager->uploadChecks);
    }

    public function testRejectsInvalidUploadData(): void
    {
        $manager = new TestFileManager('files');
        $error = null;

        self::assertSame('', $manager->storeUploadedFile(['name' => 'example.txt'], '', '', $error));
        self::assertSame('Invalid uploaded file data.', $error);
    }

    public function testRejectsDisallowedTypeAndExtension(): void
    {
        $source = $this->createSourceFile('contents');
        $manager = new TestFileManager('files');
        $error = null;

        self::assertSame('', $manager->storeUploadedFile(
            $this->upload('example.txt', $source, 'application/json'),
            '',
            '',
            $error,
        ));
        self::assertIsString($error);
        self::assertStringContainsString('not supported', $error);

        self::assertSame('', $manager->storeUploadedFile(
            $this->upload('example.csv', $source, 'text/plain'),
            '',
            '',
            $error,
        ));
        self::assertIsString($error);
        self::assertStringContainsString('not supported', $error);
    }

    public function testStoresUploadedFileWithNormalizedPath(): void
    {
        $source = $this->createSourceFile('contents');
        $manager = new TestFileManager('files');
        $error = 'previous';

        $fileName = $manager->storeUploadedFile(
            $this->upload('example.txt', $source),
            '\\uploads\\documents/',
            '',
            $error,
        );

        self::assertSame('example.txt', $fileName);
        self::assertNull($error);
        self::assertSame('contents', file_get_contents($this->root . '/uploads/documents/example.txt'));
    }

    public function testUsesSaveAsAndGeneratesUniqueNameForDuplicate(): void
    {
        $manager = new TestFileManager('files');
        $error = null;

        $first = $manager->storeUploadedFile(
            $this->upload('first.txt', $this->createSourceFile('first')),
            '',
            '../saved',
            $error,
        );
        $second = $manager->storeUploadedFile(
            $this->upload('second.txt', $this->createSourceFile('second')),
            '',
            'saved',
            $error,
        );

        self::assertSame('saved.txt', $first);
        self::assertNotSame($first, $second);
        self::assertStringEndsWith('_saved.txt', $second);
    }

    public function testReportsStreamOpenFailure(): void
    {
        $manager = new TestFileManager('files');
        $error = null;

        $result = $manager->storeUploadedFile(
            $this->upload('example.txt', $this->root . '/missing.txt'),
            '',
            '',
            $error,
        );

        self::assertSame('', $result);
        self::assertSame('Failed to open uploaded file.', $error);
    }

    public function testDeletesExistingFileAndIgnoresMissingFile(): void
    {
        $manager = new TestFileManager('files');
        file_put_contents($this->root . '/delete.txt', 'contents');

        $manager->deleteFileIfExist('delete.txt');
        $manager->deleteFileIfExist('delete.txt');

        self::assertFileDoesNotExist($this->root . '/delete.txt');
    }

    public function testUniqueFileNamePreservesOriginalName(): void
    {
        $name = (new TestFileManager('files'))->getUniqueFileName('example.txt');

        self::assertStringEndsWith('_example.txt', $name);
        self::assertNotSame('example.txt', $name);
    }

    /** @return array{name: string, tmp_name: string, type: string, error: int} */
    private function upload(string $name, string $path, string $type = 'text/plain'): array
    {
        return [
            'name' => $name,
            'tmp_name' => $path,
            'type' => $type,
            'error' => UPLOAD_ERR_OK,
        ];
    }

    private function createSourceFile(string $contents): string
    {
        $path = $this->root . '/source-' . bin2hex(random_bytes(4));
        file_put_contents($path, $contents);

        return $path;
    }

    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $items = scandir($directory);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory . '/' . $item;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($directory);
    }
}

final class TestFileManager extends FileManager
{
    public int $uploadChecks = 0;

    protected function isUploadedFile(string $file_path): bool
    {
        $this->uploadChecks++;

        return true;
    }
}
