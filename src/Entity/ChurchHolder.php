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
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Templom gondnokság jegyzése.
 */
#[ORM\Entity(repositoryClass: ChurchRepository::class)]
#[ORM\Table(name: 'church_holders')]
class ChurchHolder implements EntityModificationDateTimeInterface
{
    use EntityModificationDateTimeTrait;

    #[ORM\Id]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\GeneratedValue('AUTO')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'uid', nullable: false)]
    private ?User $user = null;

    #[ORM\OneToOne(inversedBy: 'holder', targetEntity: Church::class)]
    #[ORM\JoinColumn(name: 'church_id', referencedColumnName: 'id', nullable: false)]
    private ?Church $church = null;

    #[ORM\Column(name: 'description', type: Types::STRING, length: 255, nullable: true)]
    private ?string $description;

    /**
     * @todo milyen statuszok vannak?
     * @todo ez integer is lehet
     * @todo uj nevezek a holder status
     */
    public const STATUS_LEFT = 'left';
    public const STATUS_ASKED = 'asked';
    public const STATUS_ALLOWED = 'allowed';
    public const STATUS_DENIED = 'denied';
    public const STATUS_REVOKED = 'revoked';

    public const HOLDER_STATUS_NA = -1;
    public const HOLDER_STATUS_ORPHAN = 0;
    public const HOLDER_STATUS_ALLOWED = 1;
    public const HOLDER_STATUS_DENIED = 2;
    public const HOLDER_STATUS_REVOKED = 3;
    public const HOLDER_STATUS_ASKED = 4;
    public const HOLDER_STATUS_LEFT = 5; // todo left az orphan? mikor left?

    /**
     * @todo ez nem inkabb egy integer?
     */
    #[ORM\Column(name: 'status', type: Types::STRING, length: 32, nullable: false, options: ['default' => self::STATUS_ASKED])]
    private string $status = self::STATUS_ASKED;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getChurch(): ?Church
    {
        return $this->church;
    }

    public function setChurch(?Church $church): void
    {
        $this->church = $church;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }
}
