<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @todo slug bevezetese
 */
#[ORM\Entity]
#[ORM\Table(name: 'templomok')]
class Church
{
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

    public function getId(): ?int
    {
        return $this->id;
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
    public function getKnownName(): ?string
    {
        return $this->knownName;
    }

    /**
     * @param string|null $knownName
     */
    public function setKnownName(?string $knownName): void
    {
        $this->knownName = $knownName;
    }

    /**
     * @return int|null
     */
    public function getCountry(): ?int
    {
        return $this->country;
    }

    /**
     * @param int|null $country
     */
    public function setCountry(?int $country): void
    {
        $this->country = $country;
    }

    /**
     * @return int|null
     */
    public function getCounty(): ?int
    {
        return $this->county;
    }

    /**
     * @param int|null $county
     */
    public function setCounty(?int $county): void
    {
        $this->county = $county;
    }

}
