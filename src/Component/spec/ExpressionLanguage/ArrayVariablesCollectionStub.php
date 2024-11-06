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

namespace spec\Sylius\Component\Grid\ExpressionLanguage;

use Sylius\Component\Grid\ExpressionLanguage\VariablesCollectionInterface;

final class ArrayVariablesCollectionStub implements VariablesCollectionInterface
{
    public function __construct(private array $variables)
    {
    }

    public function getCollection(): array
    {
        return $this->variables;
    }
}
