<?php

/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Mep\WebToolkitBundle\Form\TypeGuesser;

use Mep\WebToolkitBundle\Dto\AttachmentAssociationContextDto;
use Mep\WebToolkitBundle\Entity\Attachment;
use Mep\WebToolkitBundle\Form\AdminAttachmentType;
use Mep\WebToolkitBundle\Validator\AttachmentFile;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;
use Symfony\Component\Form\Guess\ValueGuess;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class AdminAttachmentTypeGuesser implements FormTypeGuesserInterface
{
    public function guessType(string $class, string $property): ?TypeGuess
    {
        $reflectionProperty = new ReflectionProperty($class, $property);
        $reflectionType = $reflectionProperty->getType();

        if (! $reflectionType instanceof ReflectionType) {
            return null;
        }

        if ($reflectionType instanceof ReflectionNamedType && Attachment::class !== $reflectionType->getName()) {
            return null;
        }

        if ($reflectionType instanceof ReflectionUnionType) {
            $isValid = false;

            foreach ($reflectionType->getTypes() as $type) {
                if (Attachment::class === $type->getName()) {
                    $isValid = true;

                    break;
                }
            }

            if (! $isValid) {
                return null;
            }
        }

        $validAttachmentAttribute = ($reflectionProperty->getAttributes(AttachmentFile::class)[0] ?? null)
            ?->newInstance()
        ;

        return new TypeGuess(
            AdminAttachmentType::class,
            $validAttachmentAttribute instanceof AttachmentFile ? [
                AdminAttachmentType::MAX_SIZE => $validAttachmentAttribute->maxSize,
                AdminAttachmentType::ALLOWED_MIME_TYPES => $validAttachmentAttribute->allowedMimeTypes,
                AdminAttachmentType::ALLOWED_NAME_PATTERN => $validAttachmentAttribute->allowedNamePattern,
                AdminAttachmentType::METADATA => $validAttachmentAttribute->metadata,
                AdminAttachmentType::PROCESSORS_OPTIONS => $validAttachmentAttribute->processorsOptions,
                AdminAttachmentType::CONTEXT => (string) new AttachmentAssociationContextDto($class, $property),
            ] : [],
            Guess::VERY_HIGH_CONFIDENCE,
        );
    }

    public function guessRequired(string $class, string $property): ?ValueGuess
    {
        return null;
    }

    public function guessMaxLength(string $class, string $property): ?ValueGuess
    {
        return null;
    }

    public function guessPattern(string $class, string $property): ?ValueGuess
    {
        return null;
    }
}
