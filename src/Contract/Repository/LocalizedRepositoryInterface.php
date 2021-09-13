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

use Doctrine\ORM\QueryBuilder;

interface LocalizedRepositoryInterface
{
    public function createLocalizedQueryBuilder(string $alias, ?string $indexBy = null): QueryBuilder;

    public function localizeQueryBuilder(QueryBuilder $queryBuilder): QueryBuilder;
}
