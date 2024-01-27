<?php

namespace App\Legacy;

use Psr\Container\ContainerInterface;

interface ContainerAwareInterface
{
    public function setContainer(ContainerInterface $container): ?ContainerInterface;
}