<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\Component\Grid\Tests\Dummy;


use Sylius\Bundle\GridBundle\AbstractGrid;
use Sylius\Bundle\GridBundle\Builder\GridBuilderInterface;
use Sylius\Bundle\GridBundle\ResourceAwareGridInterface;

final class BarGrid extends AbstractGrid implements ResourceAwareGridInterface
{
    public static function getName(): string
    {
        return 'app_bar';
    }

    public static function getResourceClass(): string
    {
        return Bar::class;
    }

    public function buildGrid(GridBuilderInterface $gridBuilder): void
    {
    }
}
