<?php

declare(strict_types=1);

namespace DZunke\NovDoc\Infrastructure\Repository;

use DateTimeImmutable;
use DZunke\NovDoc\Domain\Document\Directory;
use DZunke\NovDoc\Domain\Library\Image\Image;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Finder\Finder;

use function array_filter;
use function assert;
use function file_put_contents;
use function is_array;
use function json_decode;
use function json_encode;
use function strcasecmp;
use function usort;

use const DIRECTORY_SEPARATOR;
use const JSON_PRETTY_PRINT;

class FilesystemImageRepository
{
    public function __construct(
        private readonly string $libraryImageStoragePath,
        private readonly LoggerInterface $logger,
        private readonly FilesystemDirectoryRepository $directoryRepository,
    ) {
    }

    public function store(Image $image): void
    {
        // When stored it is updated! Maybe change later with a change detection ... but yeah .. it is changed for now
        $image->updatedAt = new DateTimeImmutable();

        $filename        = $image->id . '.json';
        $filepath        = $this->libraryImageStoragePath . DIRECTORY_SEPARATOR . $filename;
        $documentAsArray = $image->toArray();
        $documentAsJson  = json_encode($documentAsArray, JSON_PRETTY_PRINT);

        file_put_contents($filepath, $documentAsJson);
    }

    /** @return list<Image> */
    public function findAll(): array
    {
        $finder = (new Finder())
            ->ignoreDotFiles(true)
            ->in($this->libraryImageStoragePath)
            ->files();

        $images = [];
        foreach ($finder as $imageFound) {
            try {
                $images[] = $this->convertJsonToImage($imageFound->getContents());
            } catch (RuntimeException $e) {
                $this->logger->error($e, ['image' => $imageFound]);
            }
        }

        usort(
            $images,
            static fn (Image $left, Image $right) => strcasecmp($left->title, $right->title),
        );

        return $images;
    }

    /** @return list<Image> */
    public function findByDirectory(Directory $directory): array
    {
        $images = $this->findAll();

        return array_filter($images, static function (Image $document) use ($directory) {
            return $document->directory->id === $directory->id;
        });
    }

    private function convertJsonToImage(string $json): Image
    {
        $imageArr = json_decode($json, true);

        if (! is_array($imageArr)) {
            throw new RuntimeException('Document to load contain invalid content.');
        }

        $image = new Image(
            $imageArr['title'],
            $imageArr['mime_type'],
            $imageArr['encoded_image'],
            $imageArr['description'],
        );

        $image->id        = $imageArr['id'];
        $image->updatedAt = new DateTimeImmutable($imageArr['last_updated']);

        $directory = $this->directoryRepository->findById($imageArr['directory']);
        assert($directory instanceof Directory);

        $image->directory = $directory;

        return $image;
    }
}
