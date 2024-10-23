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

namespace App\Filter;

use App\Grid\Type\NationalityFilterType;
use Sylius\Component\Grid\Attribute\AsFilter;
use Sylius\Component\Grid\Data\DataSourceInterface;
use Sylius\Component\Grid\Filter\EntityFilter;
use Sylius\Component\Grid\Filtering\FilterInterface;

#[AsFilter(
    formType: NationalityFilterType::class,
)]
final class AttributeNationalityFilter implements FilterInterface
{
    public function __construct(private EntityFilter $decorated)
    {
    }

    public function apply(DataSourceInterface $dataSource, string $name, $data, array $options): void
    {
        $this->decorated->apply($dataSource, $name, $data, $options);
    }
}
