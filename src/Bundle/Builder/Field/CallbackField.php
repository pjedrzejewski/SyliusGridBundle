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

namespace Sylius\Bundle\GridBundle\Builder\Field;

final class CallbackField
{
    public static function create(string $name, callable $callback, bool $htmlspecialchars = true): FieldInterface
    {
        return Field::create($name, 'callback')
            ->setOption('callback', $callback)
            ->setOption('htmlspecialchars', $htmlspecialchars)
        ;
    }
}
