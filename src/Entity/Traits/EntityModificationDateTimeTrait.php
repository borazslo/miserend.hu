<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait EntityModificationDateTimeTrait
{
    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE, nullable: false)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_IMMUTABLE, nullable: false)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(name: 'deleted_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function isDeleted(\DateTimeInterface $at = new \DateTimeImmutable()): bool
    {
        return $this->deletedAt !== null && $this->deletedAt <= $at;
    }

    public function createEntity(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
            $this->updateEntity();
        }
    }

    public function updateEntity(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function deleteFrom(?\DateTimeImmutable $from = null): void
    {
        if ($this->deletedAt !== null) {
            $this->deletedAt = $from ?? new \DateTimeImmutable();
        }
    }

    public function undelete(): void
    {
        $this->deletedAt = null;
    }
}
