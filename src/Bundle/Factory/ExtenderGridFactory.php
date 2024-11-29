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

namespace Sylius\Bundle\GridBundle\Factory;

use Sylius\Bundle\GridBundle\Registry\GridRegistryInterface;
use Sylius\Component\Grid\Configuration\GridConfigurationExtenderInterface;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Factory\GridFactoryInterface;
use Webmozart\Assert\Assert;

final class ExtenderGridFactory implements GridFactoryInterface
{
    public function __construct(
        private GridFactoryInterface $decorated,
        private GridRegistryInterface $gridRegistry,
        private array $gridConfigurations,
        private GridConfigurationExtenderInterface $gridConfigurationExtender,
    ) {
    }

    public function create(string $code, array $gridConfiguration): Grid
    {
        $parentGridCode = $gridConfiguration['extends'] ?? null;

        if (null !== $parentGridCode) {
            $parentGridConfiguration = $this->gridRegistry->getGrid($parentGridCode)?->toArray() ?? $this->gridConfigurations[$parentGridCode] ?? null;
            Assert::notNull($parentGridConfiguration, sprintf('Parent grid with code "%s" does not exists.', $gridConfiguration['extends']));

            $gridConfiguration = $this->gridConfigurationExtender->extends($gridConfiguration, $parentGridConfiguration);
        }

        return $this->decorated->create($code, $gridConfiguration);
    }
}
