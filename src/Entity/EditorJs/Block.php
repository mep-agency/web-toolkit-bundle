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

namespace Mep\WebToolkitBundle\Entity\EditorJs;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Mep\WebToolkitBundle\Entity\EditorJs\Block\Delimiter;
use Mep\WebToolkitBundle\Entity\EditorJs\Block\Embed;
use Mep\WebToolkitBundle\Entity\EditorJs\Block\Header;
use Mep\WebToolkitBundle\Entity\EditorJs\Block\Image;
use Mep\WebToolkitBundle\Entity\EditorJs\Block\NestedList;
use Mep\WebToolkitBundle\Entity\EditorJs\Block\Paragraph;
use Mep\WebToolkitBundle\Entity\EditorJs\Block\Quote;
use Mep\WebToolkitBundle\Entity\EditorJs\Block\Raw;
use Mep\WebToolkitBundle\Entity\EditorJs\Block\Table;
use Mep\WebToolkitBundle\Entity\EditorJs\Block\Warning;
use Stringable;
use Symfony\Component\Serializer\Annotation\DiscriminatorMap;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Uid\Uuid;

/**
 * @final You should not extend this class.
 *
 * @author Marco Lipparini <developer@liarco.net>
 */
#[ORM\Entity]
#[ORM\Table(name: 'mwt_editor_js_block')]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap(Block::BLOCKS_MAPPING)]
#[DiscriminatorMap(
    typeProperty: 'type',
    mapping: Block::BLOCKS_MAPPING,
)]
abstract class Block implements JsonSerializable, Stringable
{
    /**
     * @var array<string, class-string<Block>>
     */
    public const BLOCKS_MAPPING = [
        'paragraph' => Paragraph::class,
        'header' => Header::class,
        'list' => NestedList::class,
        'delimiter' => Delimiter::class,
        'quote' => Quote::class,
        'warning' => Warning::class,
        'image' => Image::class,
        'embed' => Embed::class,
        'table' => Table::class,
        // TODO: Implement attaches block (EditorJs)
        //'attaches' => Block\Attaches::class,
        'raw' => Raw::class,
    ];

    /**
     * @var array<class-string, string>
     */
    private static array $reverseBlocksMapping = [];

    /**
     * @var array<string>
     */
    private static array $supportedTypes = [];

    /**
     * @var array<class-string>
     */
    private static array $supportedClasses = [];

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $uuid;

    #[ORM\ManyToOne(targetEntity: EditorJsContent::class, inversedBy: 'blocks')]
    #[ORM\JoinColumn(nullable: false)]
    #[Ignore]
    private EditorJsContent $parent;

    public function __construct(
        #[ORM\Column(type: 'string', length: 255)]
        private string $id,
    ) {
        $this->uuid = Uuid::v6();
    }

    public function __toString(): string
    {
        return '';
    }

    /**
     * @param class-string $fqcn
     */
    public static function getTypeByClass(string $fqcn): string
    {
        if (empty(self::$reverseBlocksMapping)) {
            foreach (self::BLOCKS_MAPPING as $type => $class) {
                self::$reverseBlocksMapping[$class] = $type;
            }
        }

        return self::$reverseBlocksMapping[$fqcn];
    }

    /**
     * @return string[]
     */
    public static function getSupportedTypes(): array
    {
        if (empty(self::$supportedTypes)) {
            self::$supportedTypes = array_keys(self::BLOCKS_MAPPING);
        }

        return self::$supportedTypes;
    }

    /**
     * @return class-string[]
     */
    public static function getSupportedClasses(): array
    {
        if (empty(self::$supportedClasses)) {
            self::$supportedClasses = array_values(self::BLOCKS_MAPPING);
        }

        return self::$supportedClasses;
    }

    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getParent(): EditorJsContent
    {
        return $this->parent;
    }

    public function setParent(EditorJsContent $editorJsContent): self
    {
        $this->parent = $editorJsContent;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'type' => self::getTypeByClass(static::class),
            'data' => $this->getData(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    abstract protected function getData(): array;
}
