<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Services;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ConfigProvider
{
    public function __construct(
        #[Autowire(param: 'kernel.project_dir')]
        private readonly string $projectDir,
        #[Autowire(env: 'APP_ENV')]
        private readonly string $environment,
    ) {
    }

    protected function loadConfig(): array
    {
        $env = $this->environment;

        if (!is_file($this->projectDir.'/config/config.php')) {
            exit('Please config the legacy system at config/config.php');
        }

        $environment = include $this->projectDir.'/config/config.php';
        if (!\array_key_exists($env, $environment)) {
            $env = 'default';
        }
        $config = $environment['default'];
        $config['env'] = $env;
        if ($env != 'default') {
            overrideArray($config, $environment[$env]);
        }
        putenv('MISEREND_WEBAPP_ENVIRONMENT='.$env);

        return $config;
    }

    private array $config;

    public function getConfig(): array
    {
        if (!isset($this->config)) {
            $this->config = $this->loadConfig();
        }

        return $this->config;
    }
}
