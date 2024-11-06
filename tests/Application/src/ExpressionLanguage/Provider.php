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

namespace App\ExpressionLanguage;

use Sylius\Component\Grid\Attribute\AsExpressionProvider;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

#[AsExpressionProvider]
final class Provider implements ExpressionFunctionProviderInterface
{
    public function getFunctions(): array
    {
        return [
            new ExpressionFunction('get_currency_symbol', fn () => null, fn ($arguments, string $code): string => match ($code) {
                'EUR' => '€',
                'GBP' => '£',
            }),
        ];
    }
}
