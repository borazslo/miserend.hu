<?php

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