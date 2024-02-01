<?php

namespace App\Components\ApiClients\Response;

class Community
{
    private ?string $name = null;
    private ?string $ageGroup = null;
    private ?string $description = null;
    private ?string $tags = null;
    private ?string $url = null;

    public static function initWithArray(array $response): self
    {
        $object = new self();

        $object->name = $response['name'] ?? null;
        $object->ageGroup = $response['age_group '] ?? null; // BUG space in key
        $object->description = $response['description'] ?? null;
        // $object->tags = $response['tags'] ?? null; // broken
        $object->url = $response['link'] ?? null;

        return $object;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getAgeGroup(): ?string
    {
        return $this->ageGroup;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }
}
