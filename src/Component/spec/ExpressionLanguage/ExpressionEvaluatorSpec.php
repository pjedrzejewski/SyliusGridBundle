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

use PhpSpec\ObjectBehavior;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class ExpressionEvaluatorSpec extends ObjectBehavior
{
    function let(): void
    {
        $this->beConstructedWith(
            new ExpressionLanguage(),
            new ArrayVariablesCollectionStub([
                'foo' => 69,
            ]),
        );
    }

    function it_evaluate_simple_expression(): void
    {
        $this->evaluateExpression('1 + 2')->shouldReturn(3);
    }

    function it_evaluate_simple_expression_with_variable(): void
    {
        $this->evaluateExpression('1 + 2 + bar', ['bar' => 10])->shouldReturn(13);
    }

    function it_evaluate_simple_expression_with_variable_from_variable_collection(): void
    {
        $this->evaluateExpression('foo ~ " lyon"')->shouldReturn('69 lyon');
    }
}
