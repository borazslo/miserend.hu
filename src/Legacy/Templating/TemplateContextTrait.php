<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Templating;

trait TemplateContextTrait
{
    protected array $contextVariables = [];

    protected function getContextVariables(): array
    {
        return $this->contextVariables;
    }

    protected function addContextVariable(string $key, mixed $variable): void
    {
        $this->{$key} = $variable; // bc
        $this->contextVariables[$key] = $variable;
    }
}
