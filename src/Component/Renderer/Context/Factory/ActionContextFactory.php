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

namespace Sylius\Component\Grid\Renderer\Context\Factory;

use Sylius\Component\Grid\Definition\Action;
use Sylius\Component\Grid\View\GridViewInterface;

final class ActionContextFactory implements ActionContextFactoryInterface
{
    public function create(GridViewInterface $gridView, Action $action, mixed $data = null): array
    {
        return [
            'grid' => $gridView,
            'action' => $action,
            'data' => $data,
        ];
    }
}
