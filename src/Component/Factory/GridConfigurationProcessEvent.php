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

namespace Sylius\Component\Grid\Factory;

use SyliusLabs\Polyfill\Symfony\EventDispatcher\Event;

final class GridConfigurationProcessEvent extends Event
{
    public function __construct(
        private string $gridCode,
        private array $gridConfiguration,
    ) {
    }

    public function getGridCode(): string
    {
        return $this->gridCode;
    }

    public function getGridConfiguration(): array
    {
        return $this->gridConfiguration;
    }

    public function setGridConfiguration(array $gridConfiguration): void
    {
        $this->gridConfiguration = $gridConfiguration;
    }
}
