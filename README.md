# php-filemanager

`websk/php-filemanager` provides one file-management API for local, Amazon S3-compatible, and SFTP storage through Flysystem 3.

## Requirements

- PHP 8.3, 8.4, or 8.5
- `ext-fileinfo`
- `ext-mbstring`

## Installation

Install the library with the local storage adapter:

```shell
composer require websk/php-filemanager
```

S3 and SFTP support are optional. Install only the backend used by the application:

```shell
composer require league/flysystem-aws-s3-v3:^3.35
composer require league/flysystem-sftp-v3:^3.36
```

Selecting an optional adapter without its package throws a `LogicException` containing the required Composer command.

## Configuration

`FileManager` reads storage definitions from the `storages` key in `WebSK\Config\ConfWrapper`. When using the bundled configuration file, pass its `settings` section to the wrapper:

```php
<?php

use WebSK\Config\ConfWrapper;
use WebSK\FileManager\FileManager;

$config = require __DIR__ . '/config/config.default.php';
ConfWrapper::setConfig($config['settings']);

$fileManager = new FileManager('files');
```

### Local storage

```php
ConfWrapper::setConfig([
    'storages' => [
        'files' => [
            'adapter' => 'local',
            'root_path' => '/var/www/app/public/files',
            'url_path' => '/files',
            'allowed_extensions' => ['jpg', 'png', 'pdf'],
            'allowed_types' => ['image/jpeg', 'image/png', 'application/pdf'],
        ],
    ],
]);
```

`root_path` must be a non-empty string. Flysystem paths passed to the library are normalized to forward slashes.

### Amazon S3

The AWS SDK credential chain is used when `access_key` and `secret_key` are omitted:

```php
[
    'adapter' => 's3',
    'bucket' => 'application-files',
    'region' => 'eu-central-1',
    'url_path' => 'https://application-files.s3.eu-central-1.amazonaws.com',
]
```

For MinIO or another S3-compatible endpoint, provide the endpoint and credentials together:

```php
[
    'adapter' => 's3',
    'bucket' => 'application-files',
    'region' => 'eu-central-1',
    'url' => 'http://localhost:9000',
    'use_path_style_endpoint' => true,
    'access_key' => 'access-key',
    'secret_key' => 'secret-key',
    'connect_timeout' => 2,
    'timeout' => 5,
    'retries' => 1,
]
```

`access_key` and `secret_key` must either both be set or both be omitted.

### SFTP

```php
[
    'adapter' => 'sftp',
    'host' => 'sftp.example.com',
    'port' => 22,
    'username' => 'application',
    'password' => null,
    'privateKey' => '/run/secrets/sftp_private_key',
    'passphrase' => null,
    'root' => '/uploads',
    'timeout' => 10,
    'maxTries' => 4,
    'hostFingerprint' => 'SHA256:...',
    'useAgent' => false,
]
```

`host` and `username` are required. Empty password, private-key, and passphrase values are normalized to `null`, so an empty private key does not accidentally select key authentication.

## Uploading files

Pass one entry from `$_FILES`, a storage-relative target folder, and optionally a target base name:

```php
$error = null;
$fileName = $fileManager->storeUploadedFile(
    $_FILES['document'],
    'documents/2026',
    '',
    $error,
);

if ($fileName === '') {
    throw new RuntimeException($error ?? 'Upload failed.');
}

$publicUrl = $fileManager->getFileUrl('documents/2026/' . $fileName);
```

The method checks the PHP upload error before `is_uploaded_file()`, validates configured MIME types and extensions with strict comparisons, creates a missing target directory, and closes the input stream even when Flysystem throws.

## Development

Run all local checks:

```shell
composer check
```

The command runs PHP lint, PHPUnit, and PHPStan at level `max`. Remote round-trip tests are skipped unless their environment variables are configured:

- S3: `S3_TEST_BUCKET`, with optional `S3_TEST_REGION`, `S3_TEST_ENDPOINT`, `S3_TEST_ACCESS_KEY`, and `S3_TEST_SECRET_KEY`.
- SFTP: `SFTP_TEST_HOST` and `SFTP_TEST_USERNAME`, with optional `SFTP_TEST_PASSWORD`, `SFTP_TEST_PRIVATE_KEY`, `SFTP_TEST_PASSPHRASE`, `SFTP_TEST_PORT`, and `SFTP_TEST_ROOT`.

## License

This project is licensed under the MIT License. See [LICENSE](LICENSE).
