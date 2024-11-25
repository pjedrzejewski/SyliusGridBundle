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

namespace spec\Sylius\Bundle\GridBundle\Factory;

use PhpSpec\ObjectBehavior;
use Sylius\Bundle\GridBundle\Grid\GridInterface;
use Sylius\Bundle\GridBundle\Registry\GridRegistryInterface;
use Sylius\Component\Grid\Configuration\GridConfigurationExtenderInterface;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Factory\GridFactoryInterface;

final class ExtenderGridFactorySpec extends ObjectBehavior
{
    function let(
        GridFactoryInterface $decorated,
        GridRegistryInterface $gridRegistry,
        GridConfigurationExtenderInterface $gridConfigurationExtender,
    ): void {
        $this->beConstructedWith(
            $decorated,
            $gridRegistry,
            [
                'author' => ['foo' => 'foobar'],
            ],
            $gridConfigurationExtender,
        );
    }

    function it_does_not_update_grid(
        GridFactoryInterface $decorated,
        Grid $gridDefinition,
    ): void {
        $decorated->create('book', ['config'])->willReturn($gridDefinition);

        $this->create('book', ['config'])->shouldReturn($gridDefinition);
    }

    function it_updates_grid_with_parent_from_grid_registry(
        GridFactoryInterface $decorated,
        GridRegistryInterface $gridRegistry,
        GridConfigurationExtenderInterface $gridConfigurationExtender,
        GridInterface $grid,
        Grid $gridDefinition,
    ): void {
        $gridRegistry->getGrid('author')->willReturn($grid);
        $grid->toArray()->willReturn([
            'bar' => 'baz',
        ]);

        $gridConfigurationExtender
            ->extends(['extends' => 'author'], ['bar' => 'baz'])
            ->willReturn([
                'extends' => 'author',
                'bar' => 'baz',
            ]);

        $decorated->create('author_with_books', [
                'extends' => 'author',
                'bar' => 'baz',
            ])->willReturn($gridDefinition);

        $this->create('author_with_books', [
            'extends' => 'author',
        ])->shouldReturn($gridDefinition);
    }

    function it_updates_grid_with_parent_from_grid_configurations_array(
        GridFactoryInterface $decorated,
        GridConfigurationExtenderInterface $gridConfigurationExtender,
        Grid $gridDefinition,
    ): void {
        $gridConfigurationExtender
            ->extends(['extends' => 'author'], ['foo' => 'foobar'])
            ->willReturn([
                'extends' => 'author',
                'foo' => 'foobar',
            ]);

        $decorated->create('author_with_books', [
                'extends' => 'author',
                'foo' => 'foobar',
            ])->willReturn($gridDefinition);

        $this->create('author_with_books', [
            'extends' => 'author',
        ])->shouldReturn($gridDefinition);
    }

    function it_throws_an_invalid_argument_exception_when_parent_grid_is_not_found(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('create', ['sylius_admin_book', [
            'extends' => 'not_existing_grid_code',
        ]]);
    }
}
