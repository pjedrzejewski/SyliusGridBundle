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
use Sylius\Component\Grid\Attribute\AsFilter;
use Sylius\Component\Grid\Filtering\FormTypeAwareFilterInterface;
use Sylius\Component\Grid\Filtering\TypeAwareFilterInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterFiltersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('sylius.registry.grid_filter') || !$container->hasDefinition('sylius.form_registry.grid_filter')) {
            return;
        }

        $filterRegistry = $container->getDefinition('sylius.registry.grid_filter');
        $formTypeRegistry = $container->getDefinition('sylius.form_registry.grid_filter');

        foreach ($container->findTaggedServiceIds(AsFilter::SERVICE_TAG) as $id => $attributes) {
            $this->registerFilter($filterRegistry, $formTypeRegistry, $id, $attributes);
        }

        foreach ($container->findTaggedServiceIds('sylius.legacy_grid_filter') as $id => $attributes) {
            $definition = $container->findDefinition($id);

            // Already configured with "sylius.grid_filter" tag.
            if ($definition->hasTag(AsFilter::SERVICE_TAG)) {
                continue;
            }

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

            $this->registerFilter($filterRegistry, $formTypeRegistry, $id, $attributes, $type, $formType);
        }
    }

    private function registerFilter(
        Definition $filterRegistry,
        Definition $formTypeRegistry,
        string $id,
        array $attributes,
        ?string $type = null,
        ?string $formType = null,
    ): void {
        foreach ($attributes as $attribute) {
            if (null === $type && null === ($attribute['type'] ?? null)) {
                throw new InvalidArgumentException(sprintf('Tagged grid filters needs to have "type" attribute or implements "%s".', TypeAwareFilterInterface::class));
            }

            if (null === $formType && null === ($attribute['form_type'] ?? null)) {
                throw new InvalidArgumentException(sprintf('Tagged grid filters needs to have "form_type" attribute or implements "%s".', FormTypeAwareFilterInterface::class));
            }

            $filterRegistry->addMethodCall('register', [$type ?? $attribute['type'], new Reference($id)]);
            $formTypeRegistry->addMethodCall('add', [$type ?? $attribute['type'], 'default', $formType ?? $attribute['form_type']]);
        }
    }
}
