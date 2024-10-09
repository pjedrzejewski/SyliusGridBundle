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

namespace Sylius\Bundle\GridBundle\Maker;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Exception\RuntimeCommandException;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

final class MakeGrid extends AbstractMaker
{
    public function __construct(private ?ManagerRegistry $managerRegistry = null)
    {
    }

    public static function getCommandName(): string
    {
        return 'make:grid';
    }

    public static function getCommandDescription(): string
    {
        return 'Creates a Grid for a Doctrine entity class';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->setDescription(self::getCommandDescription())
            ->addArgument('entity', InputArgument::OPTIONAL, 'Entity class to create a grid for')
            ->addOption('namespace', null, InputOption::VALUE_REQUIRED, 'Customize the namespace for generated grids', 'Grid')
        ;

        $inputConfig->setArgumentAsNonInteractive('entity');
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        if ($input->getArgument('entity')) {
            return;
        }

        $argument = $command->getDefinition()->getArgument('entity');
        $entity = $io->choice($argument->getDescription(), $this->entityChoices());

        $input->setArgument('entity', $entity);
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $class = $input->getArgument('entity');

        if (!\class_exists($class)) {
            $class = $generator->createClassNameDetails($class, 'Entity\\')->getFullName();
        }

        if (!\class_exists($class)) {
            throw new RuntimeCommandException(\sprintf('Entity "%s" not found.', $input->getArgument('entity')));
        }

        $namespace = $input->getOption('namespace');

        // strip maker's root namespace if set
        if (0 === \mb_strpos($namespace, $generator->getRootNamespace())) {
            $namespace = \mb_substr($namespace, \mb_strlen($generator->getRootNamespace()));
        }

        $namespace = \trim($namespace, '\\');

        $entity = new \ReflectionClass($class);
        $grid = $generator->createClassNameDetails($entity->getShortName(), $namespace, 'Grid');

        $repository = null === $this->managerRegistry ? null : new \ReflectionClass($this->managerRegistry->getRepository($entity->getName()));

        if (0 !== \mb_strpos($repository?->getName() ?? '', $generator->getRootNamespace())) {
            // not using a custom repository
            $repository = null;
        }

        $this->defaultFieldsFor($entity->getName());

        $generator->generateClass(
            $grid->getFullName(),
            __DIR__ . '/../Resources/config/skeleton/Grid.tpl.php',
            [
                'entity' => $entity,
                'defaultFields' => $this->defaultFieldsFor($entity->getName()),
                'repository' => $repository,
            ],
        );

        $generator->writeChanges();

        $this->writeSuccessMessage($io);
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
        // No dependencies needed
    }

    private function entityChoices(): array
    {
        $choices = [];

        foreach ($this->managerRegistry?->getManagers() ?? [] as $manager) {
            foreach ($manager->getMetadataFactory()->getAllMetadata() as $metadata) {
                $choices[] = $metadata->getName();
            }
        }

        \sort($choices);

        if (empty($choices)) {
            throw new RuntimeCommandException('No entities found.');
        }

        return $choices;
    }

    /** @param class-string $class */
    private function defaultFieldsFor(string $class): iterable
    {
        $entityManager = $this->managerRegistry?->getManagerForClass($class);

        if (!$entityManager instanceof EntityManagerInterface) {
            return [];
        }

        $metadata = $entityManager->getClassMetadata($class);
        $ids = $metadata->getIdentifierFieldNames();

        foreach ($metadata->fieldMappings as $property) {
            // ignore identifiers
            if (\in_array($property['fieldName'], $ids, true)) {
                continue;
            }

            $type = \mb_strtoupper($property['type']);

            yield $property['fieldName'] => $type;
        }

        return [];
    }
}
