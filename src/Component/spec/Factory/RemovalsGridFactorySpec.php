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
use Sylius\Component\Grid\Configuration\GridConfigurationRemovalsHandlerInterface;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Factory\GridFactoryInterface;

final class RemovalsGridFactorySpec extends ObjectBehavior
{
    function let(
        GridFactoryInterface $decorated,
        GridConfigurationRemovalsHandlerInterface $gridConfigurationRemovalsHandler,
    ): void {
        $this->beConstructedWith($decorated, $gridConfigurationRemovalsHandler);
    }

    function it_implements_grid_factory_interface(): void
    {
        $this->shouldHaveType(GridFactoryInterface::class);
    }

    function it_creates_a_grid(
        GridFactoryInterface $decorated,
        GridConfigurationRemovalsHandlerInterface $gridConfigurationRemovalsHandler,
        Grid $grid,
    ): void {
        $gridConfigurationRemovalsHandler->handle(['initial_config'])->willReturn(['new_config']);

        $decorated->create('foo', ['new_config'])->willReturn($grid);

        $this->create('foo', ['initial_config'])->shouldReturn($grid);
    }
}
