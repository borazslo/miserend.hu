<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Request;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class QueryParameterEntityResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $attributes = $argument->getAttributesOfType(MapQueryParameter::class);
        if (\count($attributes) === 0) {
            return [];
        }
        // TODO more than 1?
        if (!$request->query->has($attributes[0]->name)) {
            return [];
        }

        $entityId = $request->query->getInt($attributes[0]->name);
        $entityClass = $argument->getType();
        $findMethod = $attributes[0]->options['method'] ?? 'find';

        $repository = $this->entityManager->getRepository($entityClass);
        $entity = $repository->{$findMethod}($entityId);

        return [$entity];
    }
}
