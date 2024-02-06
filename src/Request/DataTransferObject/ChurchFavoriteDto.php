<?php

namespace App\Request\DataTransferObject;

use Symfony\Component\Validator\Constraints as Assert;

class ChurchFavoriteDto
{
    public function __construct(
        #[Assert\Choice(['add', 'del'])]
        public readonly string $method,
        #[Assert\NotBlank]
        #[Assert\GreaterThan(value: 0)]
        public readonly int $church,
    )
    {
    }
}
