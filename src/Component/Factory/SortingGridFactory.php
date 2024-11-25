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

namespace Sylius\Component\Grid\Factory;

use Sylius\Component\Grid\Configuration\GridConfigurationSortingHandlerInterface;
use Sylius\Component\Grid\Definition\Grid;

final class SortingGridFactory implements GridFactoryInterface
{
    public function __construct(
        private GridFactoryInterface $decorated,
        private GridConfigurationSortingHandlerInterface $gridConfigurationSortingHandler,
    ) {
    }

    public function create(string $code, array $gridConfiguration): Grid
    {
        $gridConfiguration = $this->gridConfigurationSortingHandler->handle($gridConfiguration);

        return $this->decorated->create($code, $gridConfiguration);
    }
}
