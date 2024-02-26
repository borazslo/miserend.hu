<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Components\ApiClients\Exceptions;

class UnexpectedTypeException extends \RuntimeException
{
    public static function unexpectedType(string $expectedType, bool|string|int|null $value): self
    {
        return new self(sprintf('Unexpected type. Expected: "%s" got: %s', $expectedType, get_debug_type($value)));
    }
}
