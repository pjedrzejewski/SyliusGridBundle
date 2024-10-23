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

namespace Sylius\Component\Grid\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class AsFilter
{
    public const SERVICE_TAG = 'sylius.grid.filter';

    /**
     * @param class-string $formType The form type class name to use for filter rendering
     */
    public function __construct(
        private readonly string $formType,
        private readonly ?string $type = null,
    ) {
    }

    public function getFormType(): string
    {
        return $this->formType;
    }

    public function getType(): ?string
    {
        return $this->type;
    }
}
