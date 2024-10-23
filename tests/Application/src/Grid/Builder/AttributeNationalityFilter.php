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

namespace App\Grid\Builder;

use App\Entity\Nationality;
use App\Filter\AttributeNationalityFilter as GridNationalityFilter;
use Sylius\Bundle\GridBundle\Builder\Filter\Filter;
use Sylius\Bundle\GridBundle\Builder\Filter\FilterInterface;

final class AttributeNationalityFilter
{
    public static function create(string $name, ?bool $multiple = null, ?array $fields = null): FilterInterface
    {
        $filter = Filter::create($name, GridNationalityFilter::class);

        $filter->setFormOptions(['class' => Nationality::class]);

        if (null !== $fields) {
            $filter->setOptions(['fields' => $fields]);
        }

        if (null !== $multiple) {
            $filter->addFormOption('multiple', $multiple);
        }

        return $filter;
    }
}
