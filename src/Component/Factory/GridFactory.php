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

use Sylius\Component\Grid\Definition\ArrayToDefinitionConverterInterface;
use Sylius\Component\Grid\Definition\Grid;

final class GridFactory implements GridFactoryInterface
{
    public function __construct(
        private ArrayToDefinitionConverterInterface $converter,
    ) {
    }

    public function create(string $code, array $gridConfiguration): Grid
    {
        return $this->converter->convert($code, $gridConfiguration);
    }
}
