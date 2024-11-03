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

namespace Sylius\Bundle\GridBundle\Tests\DependencyInjection\Compiler;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Sylius\Bundle\GridBundle\Command\StubMakeGrid;
use Sylius\Bundle\GridBundle\DependencyInjection\Compiler\RegisterStubCommandsPass;
use Sylius\Bundle\GridBundle\Maker\MakeGrid;
use Symfony\Bundle\MakerBundle\MakerBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class RegisterStubCommandsPassTest extends AbstractCompilerPassTestCase
{
    /** @test */
    public function it_registers_stub_commands_when_maker_is_not_registered(): void
    {
        $this->compile();

        $this->assertContainerBuilderHasService(StubMakeGrid::class, StubMakeGrid::class);
    }

    /** @test */
    public function it_does_not_register_stub_commands_when_maker_is_registered(): void
    {
        $this->setParameter('kernel.bundles', [MakerBundle::class]);

        $this->compile();

        $this->assertContainerBuilderNotHasService(StubMakeGrid::class);
    }

    /** @test */
    public function it_unregisters_definition_for_grid_maker_when_maker_is_not_registered(): void
    {
        $this->registerService('sylius.grid.maker', MakeGrid::class);

        $this->compile();

        $this->assertContainerBuilderNotHasService('sylius.grid.maker');
    }

    /** @test */
    public function it_does_not_unregister_definition_for_grid_maker_when_maker_is_registered(): void
    {
        $this->setParameter('kernel.bundles', [MakerBundle::class]);
        $this->registerService('sylius.grid.maker', MakeGrid::class);

        $this->compile();

        $this->assertContainerBuilderHasService('sylius.grid.maker', MakeGrid::class);
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $this->setParameter('kernel.bundles', []);

        $container->addCompilerPass(new RegisterStubCommandsPass());
    }
}
