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

namespace Sylius\Bundle\GridBundle\Doctrine\ORM;

use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\From;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Sylius\Component\Grid\Data\MemberOfAwareExpressionBuilderInterface;

final class ExpressionBuilder implements MemberOfAwareExpressionBuilderInterface
{
    private QueryBuilder $queryBuilder;

    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    public function andX(...$expressions)
    {
        return $this->queryBuilder->expr()->andX(...$expressions);
    }

    public function orX(...$expressions)
    {
        return $this->queryBuilder->expr()->orX(...$expressions);
    }

    public function comparison(string $field, string $operator, $value)
    {
        $fieldWithJoin = $this->resolveFieldByAddingJoins($field);
        $valueWithJoin = $this->resolveFieldByAddingJoins($value);

        return new Comparison($fieldWithJoin, $operator, $valueWithJoin);
    }

    public function equals(string $field, $value)
    {
        $field = $this->adjustField($field);
        $parameterName = $this->getParameterName($field);
        $this->queryBuilder->setParameter($parameterName, $value);

        return $this->queryBuilder->expr()->eq($this->resolveFieldByAddingJoins($field), ':' . $parameterName);
    }

    public function notEquals(string $field, $value)
    {
        $field = $this->adjustField($field);
        $parameterName = $this->getParameterName($field);
        $this->queryBuilder->setParameter($parameterName, $value);

        return $this->queryBuilder->expr()->neq($this->resolveFieldByAddingJoins($field), ':' . $parameterName);
    }

    public function lessThan(string $field, $value)
    {
        $field = $this->adjustField($field);
        $parameterName = $this->getParameterName($field);
        $this->queryBuilder->setParameter($parameterName, $value);

        return $this->queryBuilder->expr()->lt($this->resolveFieldByAddingJoins($field), ':' . $parameterName);
    }

    public function lessThanOrEqual(string $field, $value)
    {
        $field = $this->adjustField($field);
        $parameterName = $this->getParameterName($field);
        $this->queryBuilder->setParameter($parameterName, $value);

        return $this->queryBuilder->expr()->lte($this->resolveFieldByAddingJoins($field), ':' . $parameterName);
    }

    public function greaterThan(string $field, $value)
    {
        $field = $this->adjustField($field);
        $parameterName = $this->getParameterName($field);
        $this->queryBuilder->setParameter($parameterName, $value);

        return $this->queryBuilder->expr()->gt($this->resolveFieldByAddingJoins($field), ':' . $parameterName);
    }

    public function greaterThanOrEqual(string $field, $value)
    {
        $field = $this->adjustField($field);
        $parameterName = $this->getParameterName($field);
        $this->queryBuilder->setParameter($parameterName, $value);

        return $this->queryBuilder->expr()->gte($this->resolveFieldByAddingJoins($field), ':' . $parameterName);
    }

    public function memberOf($value, string $field)
    {
        $field = $this->adjustField($field);

        return $this->queryBuilder->expr()->isMemberOf($value, $this->resolveFieldByAddingJoins($field));
    }

    public function in(string $field, array $values)
    {
        $field = $this->adjustField($field);

        return $this->queryBuilder->expr()->in($this->resolveFieldByAddingJoins($field), $values);
    }

    public function notIn(string $field, array $values)
    {
        $field = $this->adjustField($field);

        return $this->queryBuilder->expr()->notIn($this->resolveFieldByAddingJoins($field), $values);
    }

    public function isNull(string $field)
    {
        $field = $this->adjustField($field);

        return $this->queryBuilder->expr()->isNull($this->resolveFieldByAddingJoins($field));
    }

    public function isNotNull(string $field)
    {
        $field = $this->adjustField($field);

        return $this->queryBuilder->expr()->isNotNull($this->resolveFieldByAddingJoins($field));
    }

    public function like(string $field, string $pattern)
    {
        $field = $this->adjustField($field);

        return $this->queryBuilder->expr()->like(
            (string) $this->queryBuilder->expr()->lower($this->resolveFieldByAddingJoins($field)),
            $this->queryBuilder->expr()->literal(strtolower($pattern)),
        );
    }

    public function notLike(string $field, string $pattern)
    {
        $field = $this->adjustField($field);

        return $this->queryBuilder->expr()->notLike(
            (string) $this->queryBuilder->expr()->lower($this->resolveFieldByAddingJoins($field)),
            $this->queryBuilder->expr()->literal(strtolower($pattern)),
        );
    }

    public function orderBy(string $field, string $direction)
    {
        $field = $this->adjustField($field);

        return $this->queryBuilder->orderBy($this->resolveFieldByAddingJoins($field), $direction);
    }

    public function addOrderBy(string $field, string $direction)
    {
        $field = $this->adjustField($field);

        return $this->queryBuilder->addOrderBy($this->resolveFieldByAddingJoins($field), $direction);
    }

    private function getParameterName(string $field): string
    {
        $parameterName = str_replace('.', '_', $field);

        $i = 1;
        while ($this->hasParameterName($parameterName)) {
            $parameterName .= $i;
        }

        return $parameterName;
    }

    private function hasParameterName(string $parameterName): bool
    {
        return null !== $this->queryBuilder->getParameter($parameterName);
    }

    private function adjustField(string $field): string
    {
        $rootAlias = $this->queryBuilder->getRootAliases()[0];
        if (0 === strpos($field, $rootAlias . '.')) {
            return substr_replace($field, '', 0, strlen($rootAlias) + 1);
        }

        return $field;
    }

    private function resolveFieldByAddingJoins(string $field): string
    {
        [$field, $className] = $this->getFieldDetails($field);
        $metadata = $this->queryBuilder->getEntityManager()->getClassMetadata($className);

        while (count($explodedField = explode('.', $field, 3)) === 3) {
            [$rootField, $associationField, $remainder] = $explodedField;

            if (isset($metadata->embeddedClasses[$associationField])) {
                break;
            }

            $metadata = $this->queryBuilder->getEntityManager()->getClassMetadata(
                $metadata->getAssociationMapping($associationField)['targetEntity'],
            );
            $rootAndAssociationField = sprintf('%s.%s', $rootField, $associationField);

            /** @var Join[] $joins */
            $joins = array_merge([], ...array_values($this->queryBuilder->getDQLPart('join')));
            foreach ($joins as $join) {
                if ($join->getJoin() === $rootAndAssociationField) {
                    $field = sprintf('%s.%s', (string) $join->getAlias(), $remainder);

                    continue 2;
                }
            }

            // Association alias can't start with a number
            // Mapping numbers to letters will not increase the collision probability and not lower the entropy
            $associationAlias = str_replace(
                ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
                ['g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p'],
                md5($rootAndAssociationField),
            );

            $this->queryBuilder->innerJoin($rootAndAssociationField, $associationAlias);
            $field = sprintf('%s.%s', $associationAlias, $remainder);
        }

        return $field;
    }

    /**
     * This method returns an absolute path of a property path and the FQCN of the root element.
     *
     * Given the following query:
     *
     * SELECT bo FROM App\Book bo INNER JOIN App\Author au ON bo.author_id = au.id
     *
     * It will behave as follows:
     *
     * bo.title => [book.title, App\Book]
     * title => [book.title, App\Book]
     * au => [book.author, App\Book]
     * au.name => [book.author.name, App\Book]
     */
    private function getFieldDetails(string $field): array
    {
        $rootField = explode('.', $field)[0];
        if (!in_array($rootField, $this->queryBuilder->getAllAliases(), true)) {
            $field = sprintf('%s.%s', $this->queryBuilder->getRootAliases()[0], $field);
        }

        /** @var Join[] $joins */
        $joins = array_merge([], ...array_values($this->queryBuilder->getDQLPart('join')));
        while ($explodedField = explode('.', $field, 2)) {
            $rootField = $explodedField[0];
            $remainder = $explodedField[1] ?? '';

            if (in_array($rootField, $this->queryBuilder->getRootAliases(), true)) {
                break;
            }

            foreach ($joins as $join) {
                if ($join->getAlias() === $rootField) {
                    $joinSubject = $join->getJoin();

                    if (class_exists($joinSubject)) {
                        return [$field, $joinSubject];
                    }

                    $field = rtrim(sprintf('%s.%s', $joinSubject, $remainder), '.');

                    continue 2;
                }
            }

            throw new \RuntimeException(sprintf('Could not get mapping for "%s".', $field));
        }

        /** @var From[] $froms */
        $froms = $this->queryBuilder->getDQLPart('from');
        foreach ($froms as $from) {
            if ($from->getAlias() === $rootField) {
                return [$field, $from->getFrom()];
            }
        }

        throw new \RuntimeException(sprintf('Could not get metadata for "%s".', $rootField));
    }
}
