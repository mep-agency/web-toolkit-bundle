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

namespace Mep\WebToolkitBundle\Contract\FileStorage;

use Doctrine\ORM\EntityManagerInterface;
use Mep\WebToolkitBundle\Entity\Attachment;

/**
 * @author Marco Lipparini <developer@liarco.net>
 */
interface GarbageCollectorInterface
{
    /**
     * @return iterable<Attachment>
     */
    public function collect(EntityManagerInterface $entityManager, bool $dryRun): iterable;
}
