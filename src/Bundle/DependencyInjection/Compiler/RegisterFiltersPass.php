<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\Bundle\GridBundle\DependencyInjection\Compiler;

use InvalidArgumentException;
use Sylius\Component\Grid\Filtering\FormTypeAwareFilterInterface;
use Sylius\Component\Grid\Filtering\TypeAwareFilterInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterFiltersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('sylius.registry.grid_filter') || !$container->hasDefinition('sylius.form_registry.grid_filter')) {
            return;
        }

        $registry = $container->getDefinition('sylius.registry.grid_filter');
        $formTypeRegistry = $container->getDefinition('sylius.form_registry.grid_filter');

        foreach ($container->findTaggedServiceIds('sylius.grid_filter') as $id => $attributes) {
            $type = null;
            $formType = null;

            $definition = $container->getDefinition($id);
            $class = $definition->getClass();

            if ($class !== null && is_a($class, TypeAwareFilterInterface::class, true)) {
                $type = $class::getType();
            }

            if ($class !== null && is_a($class, FormTypeAwareFilterInterface::class, true)) {
                $formType = $class::getFormType();
            }

            foreach ($attributes as $attribute) {
                if (null === $type && null === ($attribute['type'] ?? null)) {
                    throw new InvalidArgumentException(sprintf('Tagged grid filters needs to have "type" attributes or implements "%s".', TypeAwareFilterInterface::class));
                }

                if (null === $formType && null === ($attribute['form_type'] ?? null)) {
                    throw new InvalidArgumentException(sprintf('Tagged grid filters needs to have "form_type" attributes or implements %s.', FormTypeAwareFilterInterface::class));
                }

                $registry->addMethodCall('register', [$type ?? $attribute['type'], new Reference($id)]);
                $formTypeRegistry->addMethodCall('add', [$type ?? $attribute['type'], 'default', $formType ?? $attribute['form_type']]);
            }
        }
    }
}
