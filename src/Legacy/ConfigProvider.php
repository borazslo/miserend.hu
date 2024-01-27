<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy;

class ConfigProvider
{
    public function __construct(
        private readonly array $config,
    ) {
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}
