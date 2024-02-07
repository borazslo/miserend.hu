<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use App\Repository\DioceseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Egyházmegyék.
 */
#[ORM\Entity(repositoryClass: DioceseRepository::class)]
#[ORM\Table(name: 'egyhazmegye')]
class Diocese
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\GeneratedValue('AUTO')]
    private ?int $id = null;

    #[ORM\Column(name: 'nev', type: Types::STRING, length: 250, nullable: false)]
    private ?string $name = null;

    #[ORM\Column(name: 'sorrend', type: Types::INTEGER, nullable: false)]
    private ?string $recordOrder = null;

    /**
     * @TODO Túlzás a 32 hosszú string ez lehetne sima int is.
     */
    #[ORM\Column(name: 'ok', type: Types::STRING, length: 32, nullable: false)]
    private ?string $status = 'i';

    /**
     * @TODO Kapcsolás user-re?
     */
    #[ORM\Column(name: 'felelos', type: Types::STRING, length: 20, nullable: false)]
    private ?string $responsible = null;

    #[ORM\Column(name: 'email', type: Types::STRING, length: 50, nullable: true)]
    private ?string $contactEmail = null;

    /**
     * @TODO int ez is. mi is ez?
     */
    // #[ORM\Column(name: 'csakez', type: Types::STRING, length: 32, nullable: true)]
    // private ?string $contactEmail = null;

    #[ORM\Column(name: 'osmRelation', type: Types::INTEGER, nullable: true)]
    private ?string $osmId = null;

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

    public function getRecordOrder(): ?string
    {
        return $this->recordOrder;
    }

    public function setRecordOrder(?string $recordOrder): void
    {
        $this->recordOrder = $recordOrder;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    public function getResponsible(): ?string
    {
        return $this->responsible;
    }

    public function setResponsible(?string $responsible): void
    {
        $this->responsible = $responsible;
    }

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(?string $contactEmail): void
    {
        $this->contactEmail = $contactEmail;
    }

    public function getOsmId(): ?string
    {
        return $this->osmId;
    }

    public function setOsmId(?string $osmId): void
    {
        $this->osmId = $osmId;
    }
}
