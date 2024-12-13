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

use Pagerfanta\Doctrine\DBAL\QueryAdapter as DBALQueryAdapter;
use Pagerfanta\Doctrine\ORM\QueryAdapter as ORMQueryAdapter;
use Pagerfanta\Doctrine\PHPCRODM\QueryAdapter as PHPCRODMQueryAdapter;
use Sylius\Bundle\CurrencyBundle\SyliusCurrencyBundle;
use Sylius\Bundle\GridBundle\Doctrine\DBAL\Driver as DBALDriver;
use Sylius\Bundle\GridBundle\Doctrine\ORM\Driver as ORMDriver;
use Sylius\Bundle\GridBundle\Doctrine\PHPCRODM\Driver as PHPCRODMDriver;
use Sylius\Bundle\GridBundle\Grid\GridInterface;
use Sylius\Bundle\GridBundle\SyliusGridBundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

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

        foreach ($config['drivers'] as $enabledDriver) {
            if ($enabledDriver === SyliusGridBundle::DRIVER_DOCTRINE_PHPCR_ODM) {
                @trigger_error(sprintf(
                    'The "%s" driver is deprecated in Sylius 1.3. Doctrine PHPCR will no longer be supported in Sylius 2.0.',
                    SyliusGridBundle::DRIVER_DOCTRINE_PHPCR_ODM,
                ), \E_USER_DEPRECATED);
            }

            $loader->load(sprintf('services/integrations/%s.xml', $enabledDriver));
        }

        foreach ($config['grids'] as $grid) {
            $driverName = $grid['driver']['name'];

            if (ORMDriver::NAME === $driverName && !class_exists(ORMQueryAdapter::class)) {
                throw new \LogicException('Pagerfanta ORM adapter is not available. Try running "composer require pagerfanta/doctrine-orm-adapter".');
            }

            if (DBALDriver::NAME === $driverName && !class_exists(DBALQueryAdapter::class)) {
                throw new \LogicException('Pagerfanta DBAL adapter is not available. Try running "composer require pagerfanta/doctrine-dbal-adapter".');
            }

            if (PHPCRODMDriver::NAME === $driverName && !class_exists(PHPCRODMQueryAdapter::class)) {
                throw new \LogicException('Pagerfanta PHPCR-ODM adapter is not available. Try running "composer require pagerfanta/doctrine-phpcr-odm-adapter".');
            }
        }

        if (\class_exists(SyliusCurrencyBundle::class)) {
            $loader->load('services/integrations/sylius_currency_bundle.xml');
        }

        $container->registerForAutoconfiguration(GridInterface::class)
            ->addTag('sylius.grid')
        ;
    }

    public function getConfiguration(array $config, ContainerBuilder $container): Configuration
    {
        $configuration = new Configuration();

        $container->addObjectResource($configuration);

        return $configuration;
    }
}
