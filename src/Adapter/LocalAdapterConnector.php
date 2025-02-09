<?php

namespace WebSK\FileManager\Adapter;

use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\MimeTypeDetection\FinfoMimeTypeDetector;

/**
 * Class LocalAdapter
 * @package WebSK\Adapter
 */
class LocalAdapterConnector implements AdapterConnectorInterface
{
    /**
     * @param array $config
     * @return FilesystemAdapter
     */
    public function getAdapter(array $config): FilesystemAdapter
    {
        if (!array_key_exists('root_path', $config)) {
            throw new \Exception('The local connector requires root_path configuration.');
        }

        $path = $config['root_path'];
        $visibility = new PortableVisibilityConverter();
        $write_flags = $config['write_flags'] ?? LOCK_EX;
        $link_handling = $config['link_handling'] ?? LocalFilesystemAdapter::DISALLOW_LINKS;
        $mime_type_detector = new FinfoMimeTypeDetector();

        return new LocalFilesystemAdapter($path, $visibility, $write_flags, $link_handling, $mime_type_detector);
    }
}
