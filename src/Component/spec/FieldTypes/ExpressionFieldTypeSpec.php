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

namespace spec\Sylius\Component\Grid\FieldTypes;

use PhpSpec\ObjectBehavior;
use spec\Sylius\Component\Grid\ExpressionLanguage\ArrayVariablesCollectionStub;
use Sylius\Component\Grid\DataExtractor\DataExtractorInterface;
use Sylius\Component\Grid\Definition\Field;
use Sylius\Component\Grid\ExpressionLanguage\ExpressionEvaluator;
use Sylius\Component\Grid\FieldTypes\FieldTypeInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class ExpressionFieldTypeSpec extends ObjectBehavior
{
    function let(
        DataExtractorInterface $dataExtractor,
    ): void {
        $this->beConstructedWith(
            $dataExtractor,
            new ExpressionEvaluator(
                new ExpressionLanguage(),
                new ArrayVariablesCollectionStub([]),
            ),
        );
    }

    function it_is_a_grid_field_type(): void
    {
        $this->shouldImplement(FieldTypeInterface::class);
    }

    function it_uses_data_extractor_to_obtain_data_and_evaluates_it_with_htmlspecialchars(
        DataExtractorInterface $dataExtractor,
        Field $field,
    ): void {
        $dataExtractor->get($field, ['foo' => 5])->willReturn(5);

        $this->render($field, ['foo' => 5], [
            'expression' => '"<strong>" ~ value * 100 ~ "$</strong>"',
            'htmlspecialchars' => true,
        ])->shouldReturn('&lt;strong&gt;500$&lt;/strong&gt;');
    }

    function it_uses_data_extractor_to_obtain_data_and_evaluates_it_without_htmlspecialchars(
        DataExtractorInterface $dataExtractor,
        Field $field,
    ): void {
        $dataExtractor->get($field, ['foo' => 5])->willReturn(5);

        $this->render($field, ['foo' => 5], [
            'expression' => '"<strong>" ~ value * 100 ~ "$</strong>"',
            'htmlspecialchars' => false,
        ])->shouldReturn('<strong>500$</strong>');
    }
}
