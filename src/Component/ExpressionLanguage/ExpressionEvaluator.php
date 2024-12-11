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

namespace Sylius\Component\Grid\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * @experimental
 */
final class ExpressionEvaluator implements ExpressionEvaluatorInterface
{
    public function __construct(
        private ExpressionLanguage $expressionLanguage,
        private VariablesCollectionInterface $variablesCollection,
    ) {
    }

    public function evaluateExpression(string $expression, array $variables = []): mixed
    {
        return $this->expressionLanguage->evaluate(
            $expression,
            array_merge(
                $this->variablesCollection->getCollection(),
                $variables,
            ),
        );
    }
}
