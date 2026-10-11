# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [3.0.0] - 2026-10-11

This release uses a major version because it upgrades Flysystem and its
adapters from version 2 to version 3 and changes dependency installation for
remote storage backends.

### Added

- Added verified support for PHP 8.5 while retaining PHP 8.3 as the minimum
  supported version.
- Added the local Flysystem adapter as a direct dependency and declared
  `ext-fileinfo` explicitly.
- Added optional Amazon S3 and SFTP adapter suggestions with actionable
  installation messages when their packages are unavailable.
- Added support for the standard AWS credential provider chain and configurable
  S3-compatible endpoints, including MinIO path-style access.
- Added SFTP private-key, passphrase, host-fingerprint, SSH Agent, and retry
  configuration.
- Added PHPUnit 12.5 tests for adapter factories, configuration validation,
  local storage, file uploads, URL generation, and deletion behavior.
- Added environment-gated S3 and SFTP round-trip integration tests.
- Added PHPStan 2 static analysis at level max and aggregate Composer `lint`,
  `test`, `analyse`, and `check` scripts.
- Added CI checks for PHP 8.3, 8.4, and 8.5.
- Added MIT license metadata and the root `LICENSE` file.
- Added Composer archive exclusions and clean-consumer artifact verification.

### Changed

- Updated Flysystem and its Local, Amazon S3, and SFTP adapters to version 3.
- Updated `websk/php-config` from version 2.0 to version 2.2.
- Made Amazon S3 and SFTP adapters optional dependencies; the base package now
  installs only local-storage support.
- Added strict validation and normalization for storage names, adapter options,
  credentials, paths, upload metadata, MIME types, and extensions.
- Updated file operations to the Flysystem 3 API and normalized storage paths to
  forward slashes.
- Updated project metadata and documentation with installation, configuration,
  optional-backend, upload, and development instructions.

### Fixed

- Check PHP upload errors before validating temporary uploaded files.
- Prevent directory components supplied in uploaded names and `save_as` values
  from changing the target path.
- Close upload streams even when the storage write fails.
- Open upload streams in read-only binary mode and report failures explicitly.
- Use strict MIME-type and extension comparisons.
- Use `directoryExists()` when creating upload target directories.
- Normalize URL separators and avoid duplicate separators when building file
  URLs.
- Report missing, unsupported, or incorrectly configured adapters with explicit
  exceptions.
- Correct the Flysystem 2/3 Local and Amazon S3 adapter class names and
  constructor usage.

### Breaking Changes

- Flysystem 3 replaces the Flysystem 2 API used by the last tagged release.
- Applications using Amazon S3 or SFTP storage must install the corresponding
  adapter package explicitly.
- Invalid configuration values that were previously passed to dependencies are
  now rejected with `InvalidArgumentException`.

## [2.0.2] - 2025-04-20

### Fixed

- Corrected the namespace casing of Flysystem's `AwsS3V3Adapter` class so the
  Amazon S3 connector works with case-sensitive autoloaders and filesystems.

## [2.0.1] - 2025-02-09

### Added

- Added Composer installation instructions to the README.
- Added explicit return types to adapter and storage factories and to public
  `FileManager` methods.

### Changed

- Added typed configuration properties to the Amazon S3 and SFTP connectors.

### Fixed

- Updated `LocalAdapterConnector` to construct Flysystem's local adapter with a
  `PortableVisibilityConverter` and `FinfoMimeTypeDetector` in the expected
  argument positions.

## [2.0.0] - 2024-08-22

### Added

- Added an explicit PHP `^8.3` runtime requirement.
- Added typed properties and a typed `STORAGES_CONFIG_KEY` class constant.
- Added Flysystem exception declarations to file operations.
- Added an explicit `SftpConnectionProvider` for SFTP connections.

### Changed

- Updated `websk/php-config` from version 1 to version 2.0.
- Updated Flysystem and the Local, Amazon S3, and SFTP adapters from version 1
  to version 2.
- Replaced Flysystem 1 adapter and filesystem interfaces with their Flysystem 2
  equivalents.
- Updated file existence, directory creation, stream writing, and deletion to
  the Flysystem 2 API.
- Added native types to core adapter and `FileManager` state and method
  signatures.

### Breaking Changes

- PHP 8.3 or newer is required.
- Flysystem 2 replaces the Flysystem 1 API.
- `websk/php-config` 2.0 replaces version 1.x.
- SFTP configuration is now passed through a Flysystem 2
  `SftpConnectionProvider`.

## [1.0.2] - 2020-02-21

### Added

- Added `FileManager::getRootPath()` and storage `root_path` state.

## [1.0.1] - 2020-02-02

### Added

- Added a Flysystem-based storage abstraction with adapter and storage
  factories.
- Added configurable Local, Amazon S3, and SFTP adapters using Flysystem 1.
- Added `FileManager::getStorage()` for direct access to the configured
  Flysystem instance.
- Added conditional file deletion and filename collision handling through the
  configured storage backend.

### Changed

- Replaced direct local filesystem operations with storage-relative Flysystem
  operations.
- Changed `FileManager` construction from individual path and allow-list
  arguments to a named storage definition loaded through `ConfWrapper`.
- Changed configuration from flat `files_*` keys to the `storages` map with
  per-storage adapter, path, URL, extension, and MIME-type settings.
- Renamed the extension allow-list configuration from
  `files_allowed_extension` to `allowed_extensions`.
- Changed the PSR-4 mapping from an empty prefix to
  `WebSK\FileManager\` => `src/` and moved public classes accordingly.
- Added runtime dependencies on Flysystem and its Amazon S3 and SFTP adapters.

### Removed

- Removed the package-local `FileUtils` helper and the `websk/php-utils`
  dependency.

### Breaking Changes

- Replaced the original path-based `FileManager` constructor with a storage
  name.
- Replaced the original local-file methods and getters with the storage-backed
  API and configuration model.

## [1.0.0] - 2020-01-29

### Added

- Initial local file-manager implementation.
- Added uploaded-file validation by extension and MIME type.
- Added configurable local root and public URL paths through
  `websk/php-config`.
- Added unique filename generation, target-directory creation, file movement,
  deletion, and path and URL generation.
- Added `FileUtils` helpers for file downloads, file and recursive directory
  deletion, and filename validation.
- Added `ext-mbstring`, `websk/php-config` 1.x, and `websk/php-utils` 1.x as
  runtime dependencies.

[Unreleased]: https://github.com/websk/php-filemanager/compare/3.0.0...HEAD
[3.0.0]: https://github.com/websk/php-filemanager/compare/2.0.2...3.0.0
[2.0.2]: https://github.com/websk/php-filemanager/compare/2.0.1...2.0.2
[2.0.1]: https://github.com/websk/php-filemanager/compare/2.0.0...2.0.1
[2.0.0]: https://github.com/websk/php-filemanager/compare/1.0.2...2.0.0
[1.0.2]: https://github.com/websk/php-filemanager/compare/1.0.1...1.0.2
[1.0.1]: https://github.com/websk/php-filemanager/compare/1.0.0...1.0.1
[1.0.0]: https://github.com/websk/php-filemanager/releases/tag/1.0.0
