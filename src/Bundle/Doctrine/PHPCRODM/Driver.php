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

namespace Sylius\Bundle\GridBundle\Doctrine\PHPCRODM;

use Doctrine\ODM\PHPCR\DocumentManagerInterface;
use Sylius\Component\Grid\Data\DataSourceInterface;
use Sylius\Component\Grid\Data\DriverInterface;
use Sylius\Component\Grid\Parameters;

@trigger_error(sprintf('The "%s" class is deprecated since Sylius 1.3. Doctrine MongoDB and PHPCR support will no longer be supported in Sylius 2.0.', Driver::class), \E_USER_DEPRECATED);

final class Driver implements DriverInterface
{
    /**
     * Driver name
     */
    public const NAME = 'doctrine/phpcr-odm';

    /**
     * Alias to use to reference fields from the data source class.
     */
    public const QB_SOURCE_ALIAS = 'o';

    public function __construct(private ?DocumentManagerInterface $documentManager = null)
    {
    }

    public function getDataSource(array $configuration, Parameters $parameters): DataSourceInterface
    {
        if (null === $this->documentManager) {
            throw new \LogicException('Doctrine phpcr-odm is not available. Try running "composer require doctrine/phpcr-odm".');
        }

        if (!array_key_exists('class', $configuration)) {
            throw new \InvalidArgumentException('"class" must be configured.');
        }

        $repository = $this->documentManager->getRepository($configuration['class']);
        $queryBuilder = $repository->createQueryBuilder(self::QB_SOURCE_ALIAS);

        return new DataSource($queryBuilder);
    }
}
