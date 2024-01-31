<?php

namespace App\Entity\Interfaces;

interface EntityModificationDateTimeInterface
{
    public function getCreatedAt(): ?\DateTimeImmutable;
    public function getUpdatedAt(): ?\DateTimeImmutable;
    public function createEntity(): void;
    public function updateEntity(): void;
    public function deleteFrom(\DateTimeImmutable $from = null): void;
    public function undelete(): void;
}
