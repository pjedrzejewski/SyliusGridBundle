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

namespace spec\Sylius\Component\Grid\Provider;

use PhpSpec\ObjectBehavior;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Exception\UndefinedGridException;
use Sylius\Component\Grid\Factory\GridFactoryInterface;
use Sylius\Component\Grid\Provider\GridProviderInterface;

final class ArrayGridProviderSpec extends ObjectBehavior
{
    function let(
        GridFactoryInterface $gridFactory,
        Grid $firstGrid,
        Grid $secondGrid,
        Grid $thirdGrid,
    ): void {
        $gridFactory->create('sylius_admin_tax_category', ['configuration1'])->willReturn($firstGrid);
        $gridFactory->create('sylius_admin_product', ['configuration2' => 'foo'])->willReturn($secondGrid);
        $gridFactory->create('sylius_admin_order', ['configuration3'])->willReturn($thirdGrid);

        $this->beConstructedWith(
            [
                'sylius_admin_tax_category' => ['configuration1'],
                'sylius_admin_product' => ['configuration2' => 'foo'],
                'sylius_admin_order' => ['configuration3'],
            ],
            $gridFactory,
        );
    }

    function it_is_a_grid_provider(): void
    {
        $this->shouldImplement(GridProviderInterface::class);
    }

    function it_returns_grid_definition_by_name(Grid $firstGrid, Grid $secondGrid, Grid $thirdGrid): void
    {
        $this->get('sylius_admin_tax_category')->shouldBeLike($firstGrid);
        $this->get('sylius_admin_product')->shouldBeLike($secondGrid);
        $this->get('sylius_admin_order')->shouldBeLike($thirdGrid);
    }

    function it_throws_an_exception_if_grid_does_not_exist(): void
    {
        $this
            ->shouldThrow(new UndefinedGridException('sylius_admin_order_item'))
            ->during('get', ['sylius_admin_order_item'])
        ;
    }
}
