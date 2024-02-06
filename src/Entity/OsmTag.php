<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use App\Repository\OsmTagRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * OSM tag-ek
 */
#[ORM\Entity(repositoryClass: OsmTagRepository::class)]
#[ORM\Table(name: 'osmtags')]
#[ORM\UniqueConstraint(name: 'uniq_osmtype_osmid_name', columns: ['osmtype', 'osmid', 'name'])]
#[ORM\Index(columns: ['name'], name: 'index_name')]
#[ORM\Index(columns: ['name', 'value'], name: 'index_name_value')]
#[ORM\Index(columns: ['osmtype', 'osmid'], name: 'index_osm')]
class OsmTag
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\GeneratedValue('AUTO')]
    private ?int $id = null;

    #[ORM\Column(name: 'osmtype', type: Types::STRING, length: 9, nullable: false)]
    private ?string $osmType = null;

    #[ORM\Column(name: 'osmid', type: Types::STRING, length: 11, nullable: false)]
    private ?string $osmId = null;

    #[ORM\Column(name: 'name', type: Types::STRING, length: 45, nullable: false)]
    private ?string $name = null;

    #[ORM\Column(name: 'value', type: Types::STRING, length: 255, nullable: true)]
    private ?string $value = null;

    # [ORM\ManyToOne(targetEntity: Church::class, inversedBy: 'osmTags')]
    # [ORM\JoinColumn(name: 'church_id', nullable: true)]
    // private ?Church $church = null;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getOsmType(): ?string
    {
        return $this->osmType;
    }

    /**
     * @param string|null $osmType
     */
    public function setOsmType(?string $osmType): void
    {
        $this->osmType = $osmType;
    }

    /**
     * @return string|null
     */
    public function getOsmId(): ?string
    {
        return $this->osmId;
    }

    /**
     * @param string|null $osmId
     */
    public function setOsmId(?string $osmId): void
    {
        $this->osmId = $osmId;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string|null $value
     */
    public function setValue(?string $value): void
    {
        $this->value = $value;
    }

    /**
     * @return Church|null
     */
    public function getChurch(): ?Church
    {
        return $this->church;
    }

    /**
     * @param Church|null $church
     */
    public function setChurch(?Church $church): void
    {
        $this->church = $church;
    }
}
/*
 * <?xml version="1.0" encoding="utf-8"?>
<doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping https://www.doctrine-project.org/schemas/orm/doctrine-mapping.xsd">
  <entity name="App\Entity\Osmtags" table="osmtags">

    <field name="createdAt" type="datetime" column="created_at" nullable="true"/>
    <field name="updatedAt" type="datetime" column="updated_at" nullable="true"/>
  </entity>
</doctrine-mapping>

 */
