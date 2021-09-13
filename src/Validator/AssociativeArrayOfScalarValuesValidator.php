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

namespace Mep\WebToolkitBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
final class AssociativeArrayOfScalarValuesValidator extends ConstraintValidator
{
    /**
     * @param null|mixed[]|string            $metadata
     * @param AssociativeArrayOfScalarValues $constraint
     */
    public function validate($metadata, Constraint $constraint): void
    {
        if (null === $metadata || '' === $metadata) {
            return;
        }

        if (! is_array($metadata)) {
            $this->context->buildViolation('Invalid metadata value.')
                ->addViolation()
            ;

            return;
        }

        foreach ($metadata as $key => $value) {
            if (! is_string($key)) {
                $this->context->buildViolation('Metadata keys must be strings.')
                    ->addViolation()
                ;

                return;
            }

            if (! is_scalar($value)) {
                $this->context->buildViolation('Metadata values must be scalar.')
                    ->addViolation()
                ;

                return;
            }
        }
    }
}
