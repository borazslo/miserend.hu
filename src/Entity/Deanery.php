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

/**
 * Esperes kerület.
 */
#[ORM\Entity()]
#[ORM\Table(name: 'espereskerulet')]
class Deanery
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\GeneratedValue('AUTO')]
    private ?int $id = null;

    #[ORM\Column(name: 'ehm', type: Types::INTEGER)]
    private ?int $diocese = null;

    #[ORM\Column(name: 'nev', type: Types::STRING, length: 50, nullable: false)]
    private ?string $name = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDiocese(): ?int
    {
        return $this->diocese;
    }

    public function setDiocese(?int $diocese): void
    {
        $this->diocese = $diocese;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }
}
