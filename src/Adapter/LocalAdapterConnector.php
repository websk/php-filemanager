<?php

namespace WebSK\FileManager\Adapter;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\MimeTypeDetection\FinfoMimeTypeDetector;

/**
 * Class LocalAdapter
 * @package WebSK\Adapter
 */
class LocalAdapterConnector implements AdapterConnectorInterface
{
    /**
     * @param array<string, mixed> $config
     */
    public function getAdapter(array $config): FilesystemAdapter
    {
        if (!isset($config['root_path']) || !is_string($config['root_path']) || trim($config['root_path']) === '') {
            throw new \InvalidArgumentException(
                'The local connector requires a non-empty root_path configuration.'
            );
        }

        $path = $config['root_path'];
        $write_flags = $config['write_flags'] ?? LOCK_EX;
        $link_handling = $config['link_handling'] ?? LocalFilesystemAdapter::DISALLOW_LINKS;

        if (!is_int($write_flags) || !is_int($link_handling)) {
            throw new \InvalidArgumentException('Local adapter flags must be integers.');
        }

        return new LocalFilesystemAdapter(
            location: $path,
            visibility: new PortableVisibilityConverter(),
            writeFlags: $write_flags,
            linkHandling: $link_handling,
            mimeTypeDetector: new FinfoMimeTypeDetector(),
        );
    }
}
