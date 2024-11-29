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

namespace Sylius\Component\Grid\Provider;

use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Exception\UndefinedGridException;
use Sylius\Component\Grid\Factory\GridFactoryInterface;

final class ArrayGridProvider implements GridProviderInterface
{
    private GridFactoryInterface $gridFactory;

    /** @var array[] */
    private array $gridConfigurations;

    public function __construct(
        array $gridConfigurations,
        GridFactoryInterface $gridFactory,
    ) {
        $this->gridConfigurations = $gridConfigurations;
        $this->gridFactory = $gridFactory;
    }

    public function get(string $code): Grid
    {
        if (!array_key_exists($code, $this->gridConfigurations)) {
            throw new UndefinedGridException($code);
        }

        return $this->gridFactory->create($code, $this->gridConfigurations[$code]);
    }
}
