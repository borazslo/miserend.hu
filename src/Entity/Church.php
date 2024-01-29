<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use App\Entity\Interfaces\FieldModificationDateTimeInterface;
use App\Entity\Traits\FieldModificationDateTimeTrait;
use App\Repository\ChurchRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @todo softdelete bevezetese
 * @todo updated at bevezetese
 * @todo created at bevezetese
 * @todo slug update
 */
#[ORM\Entity(repositoryClass: ChurchRepository::class)]
#[ORM\Table(name: 'templomok')]
class Church implements FieldModificationDateTimeInterface
{
    use FieldModificationDateTimeTrait;

    #[ORM\Id]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\GeneratedValue('AUTO')]
    private ?int $id = null;

    #[Assert\NotBlank()]
    #[Assert\Length(max: 150)]
    #[ORM\Column(name: 'nev', type: Types::STRING, length: 150, nullable: false)]
    private ?string $name = null;

    /**
     * @todo ures stringeket atalakitani null-ra
     */
    #[Assert\Length(max: 150)]
    #[ORM\Column(name: 'ismertnev', type: Types::STRING, length: 150, nullable: true)]
    private ?string $knownName = null;

    /**
     * @todo 0 erteket atalakitani null-ra
     */
    #[ORM\Column(name: 'orszag', type: Types::INTEGER, nullable: true)]
    private ?int $country = null;

    /**
     * @todo 0 erteket atalakitani null-ra
     */
    #[ORM\Column(name: 'megye', type: Types::INTEGER, nullable: true)]
    private ?int $county = null;

    #[ORM\Column(name: 'varos', type: Types::STRING, length: 100, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(name: 'cim', type: Types::STRING, length: 150, nullable: true)]
    private ?string $address = null;

    /**
     * @todo ures stringeket atalakitani null-ra
     */
    #[ORM\Column(name: 'megkozelites', type: Types::TEXT, nullable: true)]
    private ?string $directions = null;

    // plebania

    // pleb url

    // pleb eml

    // #[ORM\Column(name: 'egyhazmegye', type: Types::INTEGER)]

    // #[ORM\Column(name: 'espereskerulet', type: Types::INTEGER)]

    #[ORM\Column(name: 'leiras', type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(name: 'megjegyzes', type: Types::TEXT)]
    private ?string $remark = null;

    #[ORM\Column(name: 'miseaktiv', type: Types::BOOLEAN)]
    private ?bool $massActive = true;

    public const MODERATION_ACCEPTED = 'i';
    public const MODERATION_AWAITING_VERIFICATION = 'f';
    public const MODERATION_DENIED = 'n';

    /**
     * @todo ez a mezo igazabol eleg ha egy integer majd at kell alakitani
     */
    #[ORM\Column(name: 'ok', type: Types::STRING, length: 5)]
    private ?string $moderation = 'f';

    #[ORM\Column(name: 'slug', type: Types::STRING, length: 150, nullable: true)]
    private ?string $slug = null;

    /**
     * Gondnokság.
     */
    #[Orm\OneToOne(mappedBy: 'church', targetEntity: ChurchHolder::class)]
    private ?ChurchHolder $holder = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getKnownName(): ?string
    {
        return $this->knownName;
    }

    public function setKnownName(?string $knownName): void
    {
        $this->knownName = $knownName;
    }

    public function getCountry(): ?int
    {
        return $this->country;
    }

    public function setCountry(?int $country): void
    {
        $this->country = $country;
    }

    public function getCounty(): ?int
    {
        return $this->county;
    }

    public function setCounty(?int $county): void
    {
        $this->county = $county;
    }

    public function getMassActive(): ?bool
    {
        return $this->massActive;
    }

    public function setMassActive(?bool $massActive): void
    {
        $this->massActive = $massActive;
    }

    /**
     * @return string|null
     */
    public function getModeration(): ?string
    {
        return $this->moderation;
    }

    /**
     * @param string|null $moderation
     */
    public function setModeration(?string $moderation): void
    {
        $this->moderation = $moderation;
    }

    /**
     * @return string|null
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @param string|null $slug
     */
    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }

    public function getHolderStatus(): int
    {
        if (null === $this->holder) {
            return ChurchHolder::HOLDER_STATUS_ORPHAN;
        }

        return match ($this->holder->getStatus()) {
            ChurchHolder::STATUS_ALLOWED => ChurchHolder::HOLDER_STATUS_ALLOWED,
            ChurchHolder::STATUS_DENIED => ChurchHolder::HOLDER_STATUS_DENIED,
            ChurchHolder::STATUS_REVOKED => ChurchHolder::HOLDER_STATUS_REVOKED,
            ChurchHolder::STATUS_ASKED => ChurchHolder::HOLDER_STATUS_ASKED,
            ChurchHolder::STATUS_LEFT => ChurchHolder::HOLDER_STATUS_LEFT,
        };
    }

    public function isAllowAskToHolder(): bool
    {
        // constant('\\App\\Entity\\ChurchHolder::HOLDER_STATUS_LEFT'), constant('\\App\\Entity\\ChurchHolder::HOLDER_STATUS_ORPHAN')
        return true; // TODO fix this
    }

    public function getHolder(): ?ChurchHolder
    {
        return $this->holder;
    }

    public function setHolder(?ChurchHolder $holder): void
    {
        $this->holder = $holder;
    }
}
