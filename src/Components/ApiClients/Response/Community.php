<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Components\ApiClients\Response;

class Community
{
    private ?string $name = null;
    private ?string $ageGroup = null;
    private ?string $description = null;
    private array $tags = [];
    private ?string $url = null;

    public static function initWithArray(array $response): self
    {
        $object = new self();

        $object->name = $response['name'] ?? null;
        $object->ageGroup = $response['age_group'] ?? null;
        $object->description = $response['description'] ?? null;
        // $object->tags = $response['tags'] ?? null; // broken
        $object->url = $response['link'] ?? null;

        return $object;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getAgeGroup(): ?string
    {
        return $this->ageGroup;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @return array<string>
     */
    public function getTags(): array
    {
        return $this->tags;
    }
}
