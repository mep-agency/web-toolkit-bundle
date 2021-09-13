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

namespace Mep\WebToolkitBundle\Dto;

use Stringable;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @internal do not use this class directly
 *
 * @author Marco Lipparini <developer@liarco.net>
 */
final class AttachmentAssociationContextDto implements Stringable
{
    public function __construct(
        #[NotBlank]
        public string $fqcn,
        #[NotBlank]
        public string $fieldName,
    ) {
    }

    public function __toString(): string
    {
        if (empty($this->fqcn) || empty($this->fieldName)) {
            return '';
        }

        return $this->fqcn.'::$'.$this->fieldName;
    }

    public static function fromString(string $context): ?self
    {
        $matches = [];

        if (1 !== preg_match(
            '#^([a-zA-Z_\x7f-\xff][a-zA-Z0-9\_\x7f-\xff]*)::\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$#',
            $context,
            $matches,
        )) {
            return null;
        }

        return new self($matches[1], $matches[2]);
    }
}
