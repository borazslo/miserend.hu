<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Components\ApiClients\Response;

use App\Components\ApiClients\Exceptions\InvalidJsonSyntaxException;

class CommunitiesResponse
{
    private ?string $instituteName = null;

    private ?string $instituteUrl = null;

    private ?string $city = null;

    private ?string $address = null;

    private array $communities = [];

    public static function initWithJsonString(string $response): self
    {
        if (!json_validate($response)) {
            throw new InvalidJsonSyntaxException('Invalid json.');
        }

        return self::initWithArray(json_decode($response, true));
    }

    public static function initWithArray(array $response): self
    {
        $object = new self();

        $object->instituteName = $response['institute']['name'] ?? null;
        $object->instituteUrl = $response['institute']['url'] ?? null;
        $object->city = $response['institute']['city'] ?? null;
        $object->address = $response['institute']['address'] ?? null;

        foreach ($response['data'] ?? [] as $community) {
            $object->communities[] = Community::initWithArray($community);
        }

        return $object;
    }

    public function getInstituteName(): ?string
    {
        return $this->instituteName;
    }

    public function getInstituteUrl(): ?string
    {
        return $this->instituteUrl;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getCommunities(): array
    {
        return $this->communities;
    }
}
