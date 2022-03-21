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

namespace Mep\WebToolkitBundle\Repository\PrivacyConsent;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use LogicException;
use Mep\WebToolkitBundle\Contract\Repository\LocalizedRepositoryInterface;
use Mep\WebToolkitBundle\Contract\Repository\LocalizedRepositoryTrait;
use Mep\WebToolkitBundle\Entity\PrivacyConsent\PrivacyConsentCategory;
use Mep\WebToolkitBundle\Entity\PrivacyConsent\PrivacyConsentService;

/**
 * @method null|PrivacyConsentService find($id, $lockMode = null, $lockVersion = null)
 * @method null|PrivacyConsentService findOneBy(array $criteria, array $orderBy = null)
 * @method PrivacyConsentService[]    findAll()
 * @method PrivacyConsentService[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<PrivacyConsentService>
 *
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
class PrivacyConsentServiceRepository extends ServiceEntityRepository implements LocalizedRepositoryInterface
{
    use LocalizedRepositoryTrait;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, PrivacyConsentService::class);
    }

    /**
     * @return PrivacyConsentService[]
     */
    public function findAllOrderedByPriority(): array
    {
        return $this->findBy([], [
            'priority' => 'DESC',
        ]);
    }

    /**
     * @return PrivacyConsentService[]
     */
    public function findRequired(): array
    {
        $query = $this->createQueryBuilder('p')
            ->innerJoin(PrivacyConsentCategory::class, 'pcc', Join::WITH, 'pcc.id = p.category')
            ->andWhere('pcc.required = :true')
            ->setParameter('true', true)
            ->getQuery()
        ;

        if (! is_array($query->getResult())) {
            throw new LogicException('Invalid query.');
        }

        return $query->getResult();
    }
}
