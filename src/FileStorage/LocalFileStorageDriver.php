<?php

declare(strict_types=1);

namespace Mep\WebToolkitBundle\FileStorage;

use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\Pure;
use Mep\WebToolkitBundle\Contract\FileStorage\FileStorageDriverInterface;
use Mep\WebToolkitBundle\Entity\Attachment;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
class LocalFileStorageDriver implements FileStorageDriverInterface
{
    public function __construct(
        private string $storagePath,
        private string $publicUrlPrefix,
        private EntityManagerInterface $entityManager,
    ) {}

    public function store(File $file, array $metadata = []): Attachment
    {
        if (! $filePath = $file->getRealPath()) {
            throw new FileNotFoundException(
                null,
                0,
                null,
                $file->getPathname()
            );
        }

        $attachment = new Attachment(
            $file->getFilename(),
            $file->getMimeType() ?? 'application/octet-stream',
            $file->getSize(),
            $metadata,
        );

        // Copy new file to storage
        $filesystem = new Filesystem();
        $filesystem->copy($filePath, $this->buildFilePath($attachment));

        $this->entityManager->persist($attachment);
        $this->entityManager->flush();

        return $attachment;
    }

    public function remove(Attachment $attachment): void
    {
        if (! is_file($filePath = $this->buildFilePath($attachment))) {
            throw new FileNotFoundException(null,
                0,
                null,
                $filePath
            );
        }

        $this->entityManager->remove($attachment);
        $this->entityManager->flush();

        unlink($filePath);
    }

    #[Pure]
    public function getPublicUrl(Attachment $attachment): string
    {
        return $this->publicUrlPrefix . '/' . $attachment->getId() . '/' . $attachment->getFileName();
    }

    #[Pure]
    private function buildFilePath(Attachment $attachment): string
    {
        return $this->storagePath . '/' . $attachment->getId() . '/' . $attachment->getFileName();
    }
}
