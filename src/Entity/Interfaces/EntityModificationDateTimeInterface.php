<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\Interfaces;

interface EntityModificationDateTimeInterface
{
    public function getCreatedAt(): ?\DateTimeImmutable;

    public function getUpdatedAt(): ?\DateTimeImmutable;

    public function createEntity(): void;

    public function updateEntity(): void;

    public function deleteFrom(?\DateTimeImmutable $from = null): void;

    public function undelete(): void;
}
