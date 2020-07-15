<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020 Andreas Möller
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/ergebnis/factory-bot
 */

namespace Ergebnis\FactoryBot\Test\Unit\Persistence;

use Doctrine\ORM;
use Ergebnis\FactoryBot\Persistence\NonPersistingStrategy;
use Ergebnis\Test\Util\Helper;
use Example\Entity;
use PHPUnit\Framework;
use Prophecy\Argument;

/**
 * @internal
 *
 * @covers \Ergebnis\FactoryBot\Persistence\NonPersistingStrategy
 */
final class NonPersistingStrategyTest extends Framework\TestCase
{
    use Helper;

    public function testPersistDoesNothing(): void
    {
        $entity = new Entity\Organization(self::faker()->userName);

        $entityManager = $this->prophesize(ORM\EntityManagerInterface::class);

        $entityManager
            ->persist(Argument::is($entity))
            ->shouldNotBeCalled();

        $strategy = new NonPersistingStrategy();

        $strategy->persist(
            $entityManager->reveal(),
            $entity
        );
    }
}
