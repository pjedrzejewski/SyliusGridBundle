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

namespace Sylius\Bundle\GridBundle\DependencyInjection;

use Sylius\Bundle\CurrencyBundle\SyliusCurrencyBundle;
use Sylius\Bundle\GridBundle\Grid\GridInterface;
use Sylius\Bundle\GridBundle\SyliusGridBundle;
use Sylius\Component\Grid\Data\DataProviderInterface;
use Sylius\Component\Grid\Filtering\FilterInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class SyliusGridExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration([], $container), $configs);
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('services.xml');

        $container->setParameter('sylius.grid.templates.action', $config['templates']['action']);
        $container->setParameter('sylius.grid.templates.bulk_action', $config['templates']['bulk_action']);
        $container->setParameter('sylius.grid.templates.filter', $config['templates']['filter']);
        $container->setParameter('sylius.grids_definitions', $config['grids']);

        $container->setAlias('sylius.grid.renderer', 'sylius.grid.renderer.twig');
        $container->setAlias('sylius.grid.bulk_action_renderer', 'sylius.grid.bulk_action_renderer.twig');
        $container->setAlias('sylius.grid.data_extractor', 'sylius.grid.data_extractor.property_access');

        $availableDrivers = $this->getAvailableDrivers($container);

        $drivers = $config['drivers'];

        // Enable all available drivers if there is no configured drivers
        $drivers = [] !== $drivers ? $drivers : $availableDrivers;

        foreach ($drivers as $enabledDriver) {
            if ($enabledDriver === SyliusGridBundle::DRIVER_DOCTRINE_PHPCR_ODM) {
                @trigger_error(sprintf(
                    'The "%s" driver is deprecated in Sylius 1.3. Doctrine PHPCR will no longer be supported in Sylius 2.0.',
                    SyliusGridBundle::DRIVER_DOCTRINE_PHPCR_ODM,
                ), \E_USER_DEPRECATED);
            }

            $loader->load(sprintf('services/integrations/%s.xml', $enabledDriver));
        }

        if (\class_exists(SyliusCurrencyBundle::class)) {
            $loader->load('services/integrations/sylius_currency_bundle.xml');
        }

        $container->registerForAutoconfiguration(GridInterface::class)
            ->addTag('sylius.grid')
        ;

        $container->registerForAutoconfiguration(FilterInterface::class)
            ->addTag('sylius.grid_filter')
        ;

        $container->registerForAutoconfiguration(DataProviderInterface::class)
            ->addTag('sylius.grid_data_provider')
        ;
    }

    public function getConfiguration(array $config, ContainerBuilder $container): Configuration
    {
        $configuration = new Configuration();

        $container->addObjectResource($configuration);

        return $configuration;
    }

    private function getAvailableDrivers(ContainerBuilder $container): array
    {
        $availableDrivers = [];

        if ($container::willBeAvailable(SyliusGridBundle::DRIVER_DOCTRINE_ORM, \Doctrine\ORM\EntityManagerInterface::class, ['doctrine/doctrine-bundle'])) {
            $availableDrivers[] = SyliusGridBundle::DRIVER_DOCTRINE_ORM;
        }

        if ($container::willBeAvailable(SyliusGridBundle::DRIVER_DOCTRINE_PHPCR_ODM, \Doctrine\ODM\PHPCR\Document\Resource::class, ['doctrine/doctrine-bundle'])) {
            $availableDrivers[] = SyliusGridBundle::DRIVER_DOCTRINE_PHPCR_ODM;
        }

        return $availableDrivers;
    }
}
