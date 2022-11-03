<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);
/**
 * This code is generated by the config converter under https://github.com/mamazu/grid-config-converter
 * Feel free to modify the code as you see fit.
 */

namespace SomeNameSpace;

use Sylius\Bundle\GridBundle\Builder\GridBuilderInterface;
use Sylius\Bundle\GridBundle\Grid\AbstractGrid;
use Sylius\Bundle\GridBundle\Grid\ResourceAwareGridInterface;

class Foo extends AbstractGrid implements ResourceAwareGridInterface
{
    public static function getName(): string
    {
        return 'foo';
    }

    public function getResourceClass(): string
    {
        return 'Sylius\\Model\\Order';
    }

    public function buildGrid(GridBuilderInterface $gridBuilder): void
    {
        $gridBuilder
            ->setDriver('doctrine/orm')
            ->setDriverOption('class', '%sylius.model.order.class%')
            ->setDriverOption('pagination', [
                'fetch_join_collection' => false,
                'use_output_walkers' => false,
            ])
        ;
    }
}
