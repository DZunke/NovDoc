<?php

declare(strict_types=1);

namespace ChronicleKeeper\Settings\Application\Service\Importer;

use ChronicleKeeper\Settings\Application\Service\FileType;
use ChronicleKeeper\Settings\Application\Service\ImportSettings;
use ChronicleKeeper\Shared\Infrastructure\Persistence\Filesystem\Contracts\FileAccess;
use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem;

use function assert;
use function str_replace;

final readonly class VectorStorageImagesImporter implements SingleImport
{
    public function __construct(
        private FileAccess $fileAccess,
    ) {
    }

    public function import(Filesystem $filesystem, ImportSettings $settings): ImportedFileBag
    {
        $importedFileBag      = new ImportedFileBag();
        $libraryDirectoryPath = 'vector/image/';

        foreach ($filesystem->listContents($libraryDirectoryPath) as $zippedFile) {
            assert($zippedFile instanceof FileAttributes);

            $filename = str_replace($libraryDirectoryPath, '', $zippedFile->path());
            assert($filename !== '');

            if ($settings->overwriteLibrary === false && $this->fileAccess->exists('vector.images', $filename)) {
                $importedFileBag->append(ImportedFile::asIgnored($filename, FileType::VECTOR_STORAGE_IMAGE));
                continue;
            }

            $content = $filesystem->read($zippedFile->path());
            $this->fileAccess->write('vector.images', $filename, $content);

            $importedFileBag->append(ImportedFile::asSuccess($filename, FileType::VECTOR_STORAGE_IMAGE));
        }

        return $importedFileBag;
    }
}
