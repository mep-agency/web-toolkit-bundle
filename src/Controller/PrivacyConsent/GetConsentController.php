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

namespace Mep\WebToolkitBundle\Controller\PrivacyConsent;

use Doctrine\ORM\EntityNotFoundException;
use Mep\WebToolkitBundle\Contract\Controller\AbstractMwtController;
use Mep\WebToolkitBundle\Entity\PrivacyConsent\PrivacyConsent;
use Mep\WebToolkitBundle\Entity\PrivacyConsent\PublicKey;
use Mep\WebToolkitBundle\Repository\PrivacyConsent\PrivacyConsentRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
class GetConsentController extends AbstractMwtController
{
    public function __construct(
        private readonly PrivacyConsentRepository $privacyConsentRepository,
        ?SerializerInterface $serializer = null,
    ) {
        parent::__construct($serializer);
    }

    public function __invoke(PublicKey $publicKey): Response
    {
        $privacyConsent = $this->privacyConsentRepository->findLatestByPublicKey($publicKey);

        if (! $privacyConsent instanceof PrivacyConsent) {
            throw new EntityNotFoundException('Token not found.');
        }

        return $this->json($privacyConsent);
    }
}
