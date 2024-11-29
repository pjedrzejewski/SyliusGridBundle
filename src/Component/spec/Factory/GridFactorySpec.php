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

namespace spec\Sylius\Component\Grid\Factory;

use PhpSpec\ObjectBehavior;
use Sylius\Component\Grid\Definition\ArrayToDefinitionConverterInterface;
use Sylius\Component\Grid\Definition\Grid;

final class GridFactorySpec extends ObjectBehavior
{
    function let(
        ArrayToDefinitionConverterInterface $converter,
    ): void {
        $this->beConstructedWith($converter);
    }

    function it_creates_a_grid(
        ArrayToDefinitionConverterInterface $converter,
        Grid $grid,
    ): void {
        $converter->convert('foo', ['config'])->willReturn($grid);

        $this->create('foo', ['config'])->shouldReturn($grid);
    }
}
