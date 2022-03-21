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

use Mep\WebToolkitBundle\Contract\Controller\AbstractMwtController;
use Mep\WebToolkitBundle\Entity\PrivacyConsent\PublicKey;
use Mep\WebToolkitBundle\Repository\PrivacyConsent\PrivacyConsentRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
class ShowHistoryController extends AbstractMwtController
{
    public function __construct(
        private readonly PrivacyConsentRepository $privacyConsentRepository,
        private readonly RequestStack $requestStack,
        ?SerializerInterface $serializer = null,
    ) {
        parent::__construct($serializer);
    }

    public function __invoke(PublicKey $publicKey): Response
    {
        $requestPage = $this->requestStack->getCurrentRequest()?->get('page');
        $requestItemsPerPage = $this->requestStack->getCurrentRequest()?->get('itemsPerPage');
        $page = is_numeric($requestPage) ? (int) $requestPage : 1;
        $itemsPerPage = is_numeric(
            $requestItemsPerPage,
        ) ? (int) $requestItemsPerPage : PrivacyConsentRepository::MAX_PRIVACY_CONSENT_PER_PAGE;
        $offset = $itemsPerPage * ($page - 1);
        $paginator = $this->privacyConsentRepository->findAllByToken($publicKey, $itemsPerPage, $offset);

        return $this->json([
            'history' => $paginator,
            'totalItems' => $paginator->count(),
            'itemsPerPage' => $itemsPerPage,
        ]);
    }
}
