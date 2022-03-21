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

namespace Mep\WebToolkitBundle\Entity\PrivacyConsent;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Component\Uid\Uuid;

/**
 * @author Marco Lipparini <developer@liarco.net>
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
#[ORM\Entity]
#[ORM\Table(name: 'mwt_privacy_consent')]
class PrivacyConsent implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: PublicKey::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'system_public_key_hash', referencedColumnName: 'hash', nullable: false)]
    private PublicKey $systemPublicKey;

    #[ORM\Column(type: Types::STRING, length: 344)]
    private string $systemSignature;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: PublicKey::class, cascade: ['persist'])]
        #[ORM\JoinColumn(name: 'user_public_key_hash', referencedColumnName: 'hash', nullable: false)]
        private PublicKey $userPublicKey,
        #[ORM\Column(type: Types::STRING, length: 344)]
        private string $userSignature,
        #[ORM\Column(type: Types::TEXT)]
        private string $data,
    ) {
        $this->id = Uuid::v6();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getSystemPublicKey(): PublicKey
    {
        return $this->systemPublicKey;
    }

    public function getSystemSignature(): string
    {
        return $this->systemSignature;
    }

    public function setSystemSignature(string $systemSignature, PublicKey $systemPublicKey): self
    {
        $this->systemSignature = $systemSignature;
        $this->systemPublicKey = $systemPublicKey;

        return $this;
    }

    public function getUserPublicKey(): PublicKey
    {
        return $this->userPublicKey;
    }

    public function getUserSignature(): string
    {
        return $this->userSignature;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function setData(string $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function verifyUserSignature(): bool
    {
        $publicKey = $this->userPublicKey->getKey();

        return $publicKey->verify($this->data, base64_decode($this->userSignature, true));
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'systemPublicKey' => base64_encode($this->getSystemPublicKey()->getKey()->toString('PKCS8')),
            'systemSignature' => $this->getSystemSignature(),
            'userPublicKey' => base64_encode($this->getUserPublicKey()->getKey()->toString('PKCS8')),
            'userSignature' => $this->getUserSignature(),
            'data' => $this->getData(),
        ];
    }
}
