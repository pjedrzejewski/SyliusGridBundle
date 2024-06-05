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

namespace Sylius\Bundle\GridBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:grid')]
final class StubMakeGrid extends StubCommand
{
}
