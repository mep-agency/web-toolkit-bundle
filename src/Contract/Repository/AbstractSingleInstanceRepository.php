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

namespace Mep\WebToolkitBundle\Contract\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template T
 * @template-extends ServiceEntityRepository<T>
 *
 * @author Marco Lipparini <developer@liarco.net>
 */
abstract class AbstractSingleInstanceRepository extends ServiceEntityRepository
{
    /**
     * @param class-string<T> $entityClass
     */
    public function __construct(ManagerRegistry $managerRegistry, string $entityClass)
    {
        parent::__construct($managerRegistry, $entityClass);
    }

    /**
     * @psalm-return T|null
     *
     * @return null|mixed
     */
    public function getInstance()
    {
        return $this->findOneBy([]);
    }
}
