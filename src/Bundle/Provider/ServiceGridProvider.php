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

namespace Sylius\Bundle\GridBundle\Provider;

use Sylius\Bundle\GridBundle\Grid\GridInterface;
use Sylius\Bundle\GridBundle\Registry\GridRegistryInterface;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Exception\UndefinedGridException;
use Sylius\Component\Grid\Factory\GridFactoryInterface;
use Sylius\Component\Grid\Provider\GridProviderInterface;

final class ServiceGridProvider implements GridProviderInterface
{
    private GridRegistryInterface $gridRegistry;

    private GridFactoryInterface $gridFactory;

    public function __construct(
        GridRegistryInterface $gridRegistry,
        GridFactoryInterface $gridFactory,
    ) {
        $this->gridRegistry = $gridRegistry;
        $this->gridFactory = $gridFactory;
    }

    public function get(string $code): Grid
    {
        if (is_a($code, GridInterface::class, true)) {
            $code = $code::getName();
        }

        $grid = $this->gridRegistry->getGrid($code);

        if (null === $grid) {
            throw new UndefinedGridException($code);
        }

        return $this->gridFactory->create($code, $grid->toArray());
    }
}
