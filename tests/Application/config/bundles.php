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

use BabDev\PagerfantaBundle\BabDevPagerfantaBundle;
use Bazinga\Bundle\HateoasBundle\BazingaHateoasBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundle;
use FOS\RestBundle\FOSRestBundle;
use JMS\SerializerBundle\JMSSerializerBundle;
use Nelmio\Alice\Bridge\Symfony\NelmioAliceBundle;
use Sylius\Bundle\GridBundle\SyliusGridBundle;
use Sylius\Bundle\ResourceBundle\SyliusResourceBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MakerBundle\MakerBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use winzou\Bundle\StateMachineBundle\winzouStateMachineBundle;

return [
    FrameworkBundle::class => ['all' => true],
    DoctrineBundle::class => ['all' => true],
    TwigBundle::class => ['all' => true],
    //FOSRestBundle::class => ['all' => true],
    //JMSSerializerBundle::class => ['all' => true],
    SyliusResourceBundle::class => ['all' => true],
    SyliusGridBundle::class => ['all' => true],
    BabDevPagerfantaBundle::class => ['all' => true],
    //BazingaHateoasBundle::class => ['all' => true],
    winzouStateMachineBundle::class => ['all' => true],
    FidryAliceDataFixturesBundle::class => ['all' => true],
    NelmioAliceBundle::class => ['all' => true],
    MakerBundle::class => ['all' => true, 'test_without_maker' => false],
];
