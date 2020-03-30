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

namespace Ergebnis\FactoryBot\Test\Unit\Exception;

use Ergebnis\FactoryBot\Exception;
use PHPUnit\Framework;

/**
 * @internal
 *
 * @covers \Ergebnis\FactoryBot\Exception\EntityDefinitionNotRegistered
 */
final class EntityDefinitionNotRegisteredTest extends Framework\TestCase
{
    public function testForReturnsException(): void
    {
        $className = 'foo';

        $exception = Exception\EntityDefinitionNotRegistered::for($className);

        $message = \sprintf(
            'An entity definition for class name "%s" has not been registered.',
            $className
        );

        self::assertSame($message, $exception->getMessage());
    }
}