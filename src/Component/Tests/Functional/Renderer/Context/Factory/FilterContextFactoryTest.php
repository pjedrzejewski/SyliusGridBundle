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

namespace Sylius\Component\Grid\Tests\Functional\Renderer\Context\Factory;

use PHPUnit\Framework\TestCase;
use Sylius\Component\Grid\Definition\Filter;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Parameters;
use Sylius\Component\Grid\Renderer\Context\Factory\FilterContextFactory;
use Sylius\Component\Grid\View\GridView;

final class FilterContextFactoryTest extends TestCase
{
    public function testItBuildAContextFactory(): void
    {
        $data = ['foo' => 'fighters'];
        $gridView = new GridView(
            $data,
            Grid::fromCodeAndDriverConfiguration('app_book', 'doctrine/orm', []),
            new Parameters(),
        );
        $filter = Filter::fromNameAndType('search', 'string');

        $contextFactory = new FilterContextFactory();

        $this->assertEquals([
            'grid' => $gridView,
            'filter' => $filter,
        ], $contextFactory->create($gridView, $filter));
    }
}
