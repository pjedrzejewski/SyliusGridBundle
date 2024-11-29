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

namespace spec\Sylius\Bundle\GridBundle\Provider;

use App\Grid\BookGrid;
use PhpSpec\ObjectBehavior;
use Sylius\Bundle\GridBundle\Grid\GridInterface;
use Sylius\Bundle\GridBundle\Provider\ServiceGridProvider;
use Sylius\Bundle\GridBundle\Registry\GridRegistryInterface;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Exception\UndefinedGridException;
use Sylius\Component\Grid\Factory\GridFactoryInterface;
use Sylius\Component\Grid\Provider\GridProviderInterface;

class ServiceGridProviderSpec extends ObjectBehavior
{
    function let(
        GridFactoryInterface $gridFactory,
        GridRegistryInterface $gridRegistry,
        GridInterface $firstGrid,
        GridInterface $secondGrid,
        GridInterface $thirdGrid,
        Grid $firstGridDefinition,
        Grid $secondGridDefinition,
        Grid $thirdGridDefinition,
    ): void {
        $gridRegistry->getGrid('sylius_admin_tax_category')->willReturn($firstGrid);
        $gridRegistry->getGrid('sylius_admin_product')->willReturn($secondGrid);
        $gridRegistry->getGrid('app_book')->willReturn($thirdGrid);

        $firstGrid->toArray()->willReturn(['configuration1']);
        $secondGrid->toArray()->willReturn(['configuration2' => 'foo']);
        $thirdGrid->toArray()->willReturn(['configuration3']);

        $gridFactory->create('sylius_admin_tax_category', ['configuration1'])->willReturn($firstGridDefinition);
        $gridFactory->create('sylius_admin_product', ['configuration2' => 'foo'])->willReturn($secondGridDefinition);
        $gridFactory->create('app_book', ['configuration3'])->willReturn($thirdGridDefinition);

        $this->beConstructedWith(
            $gridRegistry,
            $gridFactory,
        );
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(ServiceGridProvider::class);
    }

    function it_is_a_grid_provider(): void
    {
        $this->shouldImplement(GridProviderInterface::class);
    }

    function it_returns_grid_definition_by_name(Grid $firstGridDefinition, Grid $secondGridDefinition, Grid $thirdGridDefinition): void
    {
        $this->get('sylius_admin_tax_category')->shouldBeLike($firstGridDefinition);
        $this->get('sylius_admin_product')->shouldBeLike($secondGridDefinition);
        $this->get('app_book')->shouldBeLike($thirdGridDefinition);
    }

    function it_gets_grids_definitions_by_fully_qualified_class_name(
        Grid $thirdGridDefinition,
    ): void {
        $this->get(BookGrid::class)->shouldBeLike($thirdGridDefinition);
    }

    function it_throws_an_undefined_grid_exception_when_grid_is_not_found(
        GridRegistryInterface $gridRegistry,
    ): void {
        $gridRegistry->getGrid('app_book')->willReturn(null);

        $this->shouldThrow(UndefinedGridException::class)->during('get', ['app_book']);
    }
}
