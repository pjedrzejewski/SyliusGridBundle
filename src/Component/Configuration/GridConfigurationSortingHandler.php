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

namespace Sylius\Component\Grid\Configuration;

use Webmozart\Assert\Assert;

final class GridConfigurationSortingHandler implements GridConfigurationSortingHandlerInterface
{
    public function handle(array $gridConfiguration): array
    {
        if (false === isset($gridConfiguration['sorting'])) {
            return $gridConfiguration;
        }

        foreach ($gridConfiguration['sorting'] as $sorting => $order) {
            Assert::keyExists($gridConfiguration['fields'] ?? [], $sorting);

            $gridConfiguration['fields'][$sorting]['sortable'] = true;
        }

        return $gridConfiguration;
    }
}
