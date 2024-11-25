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

namespace spec\Sylius\Component\Grid\Configuration;

use PhpSpec\ObjectBehavior;
use Sylius\Component\Grid\Configuration\GridConfigurationSortingHandler;

final class GridConfigurationSortingHandlerSpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(GridConfigurationSortingHandler::class);
    }

    function it_adds_sorting(): void
    {
        $gridConfiguration = [
            'fields' => [
                'title' => [],
                'author' => ['sortable' => false],
                'price' => ['sortable' => true],
            ],
            'sorting' => [
                'title' => 'asc',
                'author' => 'asc',
                'price' => 'asc',
            ],
        ];

        $this->handle($gridConfiguration)->shouldReturn([
            'fields' => [
                'title' => ['sortable' => true],
                'author' => ['sortable' => true],
                'price' => ['sortable' => true],
            ],
            'sorting' => [
                'title' => 'asc',
                'author' => 'asc',
                'price' => 'asc',
            ],
        ]);
    }
}
