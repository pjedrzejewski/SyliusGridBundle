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

namespace Sylius\Bundle\GridBundle\Grid;

use Sylius\Bundle\GridBundle\Builder\GridBuilder;
use Sylius\Bundle\GridBundle\Builder\GridBuilderInterface;
use Sylius\Component\Grid\Attribute\AsGrid;

abstract class AbstractGrid implements GridInterface
{
    public static function getName(): string
    {
        if ($attribute = self::getAttributes()) {
            if (!empty($attribute[0])) {
                return $attribute[0]->newInstance()->name ?? static::class;
            }
        }

        return static::class;
    }

    public function getResourceClass(): string
    {
        if ($attribute = self::getAttributes()) {
            return $attribute[0]->newInstance()->resourceClass;
        }

        throw new \LogicException(sprintf(
            'You have to implement %s method or use %s attribute.',
            __METHOD__,
            AsGrid::class,
        ));
    }

    public function toArray(): array
    {
        $gridBuilder = $this->createGridBuilder();

        $this->buildGrid($gridBuilder);

        return $gridBuilder->toArray();
    }

    private function createGridBuilder(): GridBuilderInterface
    {
        if ($this instanceof ResourceAwareGridInterface || $this::getAttributes() !== []) {
            return GridBuilder::create($this::getName(), $this->getResourceClass());
        }

        return GridBuilder::create($this::getName());
    }

    /**
     * @return \ReflectionAttribute<AsGrid>[]
     */
    private static function getAttributes(): array
    {
        return (new \ReflectionClass(static::class))->getAttributes(AsGrid::class);
    }
}
