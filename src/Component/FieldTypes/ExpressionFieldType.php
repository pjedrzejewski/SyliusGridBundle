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

namespace Sylius\Component\Grid\FieldTypes;

use Sylius\Component\Grid\DataExtractor\DataExtractorInterface;
use Sylius\Component\Grid\Definition\Field;
use Sylius\Component\Grid\ExpressionLanguage\ExpressionEvaluatorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ExpressionFieldType implements FieldTypeInterface
{
    public function __construct(
        private DataExtractorInterface $dataExtractor,
        private ExpressionEvaluatorInterface $expressionEvaluator,
    ) {
    }

    public function render(Field $field, $data, array $options): string
    {
        $value = (string) $this->expressionEvaluator->evaluateExpression($options['expression'], [
            'value' => $this->dataExtractor->get($field, $data),
        ]);

        if ($options['htmlspecialchars']) {
            $value = htmlspecialchars($value);
        }

        return $value;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('expression');
        $resolver->setAllowedTypes('expression', 'string');

        $resolver->setDefault('htmlspecialchars', true);
        $resolver->setAllowedTypes('htmlspecialchars', 'bool');
    }
}
