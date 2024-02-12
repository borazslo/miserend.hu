<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
    ) {
    }
}
