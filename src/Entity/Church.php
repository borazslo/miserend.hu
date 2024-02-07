<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use App\Entity\Interfaces\EntityModificationDateTimeInterface;
use App\Entity\Traits\EntityModificationDateTimeTrait;
use App\Repository\ChurchRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @todo softdelete bevezetese
 * @todo updated at bevezetese
 * @todo created at bevezetese
 * @todo slug update
 * @todo atnevezni churches-re
 */
#[ORM\Entity(repositoryClass: ChurchRepository::class)]
#[ORM\Table(name: 'templomok')]
class Church implements EntityModificationDateTimeInterface
{
    use EntityModificationDateTimeTrait;

    /**
     * @TODO atnevezni church_id-ra
     */
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

    #[ORM\Column(name: 'osmid', type: Types::STRING, length: 11, nullable: true)]
    private ?string $osmId = null;

    #[ORM\Column(name: 'osmtype', type: Types::STRING, length: 9, nullable: true)]
    private ?string $osmType = null;

    #[ORM\Column(name: 'lat', type: Types::DECIMAL, precision: 11, scale: 7, nullable: true)]
    private ?float $latitude = null;

    #[ORM\Column(name: 'lon', type: Types::DECIMAL, precision: 11, scale: 7, nullable: true)]
    private ?float $longitude = null;

    /**
     * Gondnokság.
     */
    #[ORM\OneToOne(mappedBy: 'church', targetEntity: ChurchHolder::class)]
    private ?ChurchHolder $holder = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'favorites')]
    #[ORM\JoinTable(name: 'favorites')]
    #[ORM\JoinColumn(name: 'church_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'user_id', referencedColumnName: 'uid', unique: true)]
    private ?Collection $usersWhoFavored;

    #[ORM\OneToMany(mappedBy: 'church', targetEntity: OsmTag::class)]
    #[ORM\JoinTable(name: 'osmtags')]
    private ?Collection $osmTags = null;

    /**
     * <?xml version="1.0" encoding="utf-8"?>
     * <doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping https://www.doctrine-project.org/schemas/orm/doctrine-mapping.xsd">
     * <entity name="App\Entity\Templomok" table="templomok">
     * <indexes>
     * <index name="egyhazmegye" columns="egyhazmegye"/>
     * <index name="espereskerulet" columns="espereskerulet"/>
     * <index name="id" columns="id"/>
     * <index name="ismertnev" columns="ismertnev"/>
     * <index name="osm" columns="osmid,osmtype"/>
     * <index name="varos" columns="varos"/>
     * </indexes>
     * <id name="id" type="integer" column="id">
     * <generator strategy="IDENTITY"/>
     * </id>
     * <field name="nev" type="string" column="nev" length="150" nullable="false">
     * <options>
     * <option name="fixed"/>
     * <option name="default"/>
     * </options>
     * </field>
     * <field name="ismertnev" type="string" column="ismertnev" length="150" nullable="false">
     * <options>
     * <option name="fixed"/>
     * <option name="default"/>
     * </options>
     * </field>
     * <field name="orszag" type="integer" column="orszag" nullable="false">
     * <options>
     * <option name="unsigned"/>
     * <option name="default">0</option>
     * </options>
     * </field>
     * <field name="megye" type="integer" column="megye" nullable="false">
     * <options>
     * <option name="unsigned"/>
     * <option name="default">0</option>
     * </options>
     * </field>
     * <field name="varos" type="string" column="varos" length="100" nullable="false">
     * <options>
     * <option name="fixed"/>
     * <option name="default"/>
     * </options>
     * </field>
     * <field name="cim" type="string" column="cim" length="250" nullable="false">
     * <options>
     * <option name="fixed"/>
     * <option name="default"/>
     * </options>
     * </field>
     * <field name="megkozelites" type="text" column="megkozelites" length="255" nullable="false">
     * <options>
     * <option name="fixed"/>
     * </options>
     * </field>
     * <field name="plebania" type="text" column="plebania" length="65535" nullable="false">
     * <options>
     * <option name="fixed"/>
     * </options>
     * </field>
     * <field name="plebUrl" type="string" column="pleb_url" length="100" nullable="false">
     * <options>
     * <option name="fixed"/>
     * <option name="default"/>
     * </options>
     * </field>
     * <field name="plebEml" type="string" column="pleb_eml" length="100" nullable="false">
     * <options>
     * <option name="fixed"/>
     * <option name="default"/>
     * </options>
     * </field>
     * <field name="egyhazmegye" type="integer" column="egyhazmegye" nullable="false">
     * <options>
     * <option name="unsigned"/>
     * <option name="default">0</option>
     * </options>
     * </field>
     * <field name="espereskerulet" type="integer" column="espereskerulet" nullable="false">
     * <options>
     * <option name="unsigned"/>
     * <option name="default">0</option>
     * </options>
     * </field>
     * <field name="leiras" type="text" column="leiras" length="65535" nullable="false">
     * <options>
     * <option name="fixed"/>
     * </options>
     * </field>
     * <field name="megjegyzes" type="text" column="megjegyzes" length="65535" nullable="false">
     * <options>
     * <option name="fixed"/>
     * </options>
     * </field>
     * <field name="miseaktiv" type="integer" column="miseaktiv" nullable="true">
     * <options>
     * <option name="unsigned"/>
     * <option name="default">1</option>
     * </options>
     * </field>
     * <field name="misemegj" type="text" column="misemegj" length="65535" nullable="false">
     * <options>
     * <option name="fixed"/>
     * </options>
     * </field>
     * <field name="bucsu" type="text" column="bucsu" length="65535" nullable="false">
     * <options>
     * <option name="fixed"/>
     * </options>
     * </field>
     * <field name="frissites" type="date" column="frissites" nullable="false"/>
     * <field name="kontakt" type="string" column="kontakt" length="250" nullable="false">
     * <options>
     * <option name="fixed"/>
     * <option name="default"/>
     * </options>
     * </field>
     * <field name="kontaktmail" type="string" column="kontaktmail" length="70" nullable="false">
     * <options>
     * <option name="fixed"/>
     * <option name="default"/>
     * </options>
     * </field>
     * <field name="adminmegj" type="text" column="adminmegj" length="65535" nullable="false">
     * <options>
     * <option name="fixed"/>
     * </options>
     * </field>
     * <field name="letrehozta" type="string" column="letrehozta" length="20" nullable="false">
     * <options>
     * <option name="fixed"/>
     * <option name="default"/>
     * </options>
     * </field>
     * <field name="megbizhato" type="string" column="megbizhato" length="32" nullable="false">
     * <options>
     * <option name="fixed"/>
     * <option name="default">n</option>
     * </options>
     * </field>
     * <field name="createdAt" type="datetime" column="created_at" nullable="true"/>
     * <field name="modositotta" type="string" column="modositotta" length="20" nullable="false">
     * <options>
     * <option name="fixed"/>
     * <option name="default"/>
     * </options>
     * </field>
     * <field name="moddatum" type="datetime" column="moddatum" nullable="false"/>
     * <field name="log" type="text" column="log" length="65535" nullable="false">
     * <options>
     * <option name="fixed"/>
     * </options>
     * </field>
     * <field name="ok" type="string" column="ok" length="32" nullable="false">
     * <options>
     * <option name="fixed"/>
     * <option name="default">i</option>
     * </options>
     * </field>
     * <field name="eszrevetel" type="string" column="eszrevetel" length="32" nullable="false">
     * <options>
     * <option name="fixed"/>
     * <option name="default">n</option>
     * </options>
     * </field>
     * <field name="updatedAt" type="datetime" column="updated_at" nullable="true"/>
     * <field name="deletedAt" type="datetime" column="deleted_at" nullable="true"/>
     * <field name="osmid" type="string" column="osmid" length="11" nullable="true">
     * <options>
     * <option name="fixed"/>
     * </options>
     * </field>
     * <field name="osmtype" type="string" column="osmtype" length="9" nullable="true">
     * <options>
     * <option name="fixed"/>
     * </options>
     * </field>
     * <field name="lat" type="decimal" column="lat" precision="11" scale="7" nullable="true"/>
     * <field name="lon" type="decimal" column="lon" precision="10" scale="7" nullable="true"/>
     * </entity>
     * </doctrine-mapping>.
     */
    public function __construct()
    {
        $this->usersWhoFavored = new ArrayCollection();
        $this->osmTags = new ArrayCollection();
    }

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

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getMassActive(): ?bool
    {
        return $this->massActive;
    }

    public function setMassActive(?bool $massActive): void
    {
        $this->massActive = $massActive;
    }

    public function getModeration(): ?string
    {
        return $this->moderation;
    }

    public function setModeration(?string $moderation): void
    {
        $this->moderation = $moderation;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }

    public function getHolderStatus(): int
    {
        if ($this->holder === null) {
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

    public function getUsersWhoFavored(): array
    {
        return $this->usersWhoFavored->toArray();
    }

    public function addUserWhoFavored(User $user): void
    {
        $this->usersWhoFavored[] = $user;
    }

    public function removeUserWhoFavored(User $user): void
    {
        $this->usersWhoFavored->removeElement($user);
    }

    public function getOsmId(): ?string
    {
        return $this->osmId;
    }

    public function setOsmId(?string $osmId): void
    {
        $this->osmId = $osmId;
    }

    public function getOsmType(): ?string
    {
        return $this->osmType;
    }

    public function setOsmType(?string $osmType): void
    {
        $this->osmType = $osmType;
    }

    public function getOsmUrl(): ?string
    {
        if ($this->osmType === null || $this->osmId === null) {
            return null;
        }

        return sprintf('https://www.openstreetmap.org/%s/%s', $this->osmType, $this->osmId);
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): void
    {
        $this->latitude = $latitude;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): void
    {
        $this->longitude = $longitude;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): void
    {
        $this->remark = $remark;
    }
}
