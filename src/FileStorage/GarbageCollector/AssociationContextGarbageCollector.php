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

namespace Mep\WebToolkitBundle\FileStorage\GarbageCollector;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Generator;
use LogicException;
use Mep\WebToolkitBundle\Contract\FileStorage\GarbageCollectorInterface;
use Mep\WebToolkitBundle\Dto\AttachmentAssociationContextDto;
use Mep\WebToolkitBundle\Entity\Attachment;

/**
 * Uses Doctrine metadata to find all associations to Attachment objects and collects unused Attachments thanks to the
 * "context" metadata.
 *
 * @author Marco Lipparini <developer@liarco.net>
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
final class AssociationContextGarbageCollector implements GarbageCollectorInterface
{
    public function __construct(
        private readonly Configuration $configuration,
    ) {
    }

    /**
     * @return Generator<Attachment>
     */
    public function collect(EntityManagerInterface $entityManager, bool $dryRun): Generator
    {
        $entities = $this->configuration
            ->getMetadataDriverImpl()
            ?->getAllClassNames() ?? []
        ;

        foreach ($entities as $entity) {
            foreach ($entityManager->getClassMetadata($entity)->getAssociationMappings() as $mapping) {
                if (Attachment::class === $mapping['targetEntity']) {
                    $attachmentRepository = $entityManager->getRepository(Attachment::class);
                    /** @var string $fieldName */
                    $fieldName = $mapping['fieldName'];
                    $queryBuilder = $attachmentRepository->createQueryBuilder('a')
                        ->leftJoin($entity, 'p', Join::WITH, 'p.attachment = a.id')
                        ->andWhere('p.'.$mapping['fieldName'].' IS NULL AND a.context = :context')
                        ->setParameter(
                            'context',
                            (string) (new AttachmentAssociationContextDto($entity, $fieldName)),
                        )
                    ;

                    $results = $queryBuilder->getQuery()
                        ->getResult()
                    ;

                    if (! is_iterable($results)) {
                        throw new LogicException('Data is not of the correct type.');
                    }

                    foreach ($results as $result) {
                        if (! $result instanceof Attachment) {
                            throw new LogicException('Data is not of the correct type.');
                        }

                        yield $result;
                    }
                }
            }
        }
    }
}
