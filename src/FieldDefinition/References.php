<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020-2021 Andreas Möller
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/ergebnis/factory-bot
 */

namespace Ergebnis\FactoryBot\FieldDefinition;

use Ergebnis\FactoryBot\Count;
use Ergebnis\FactoryBot\FixtureFactory;
use Faker\Generator;

/**
 * @internal
 *
 * @phpstan-template T of object
 *
 * @psalm-template T of object
 */
final class References implements Resolvable
{
    /**
     * @phpstan-var class-string<T>
     *
     * @psalm-var class-string<T>
     *
     * @var string
     */
    private $className;

    /**
     * @var Count
     */
    private $count;

    /**
     * @var array<string, \Closure|mixed|Resolvable>
     */
    private $fieldDefinitionOverrides = [];

    /**
     * @phpstan-param class-string<T> $className
     *
     * @psalm-param class-string<T> $className
     */
    public function __construct(string $className, Count $count, array $fieldDefinitionOverrides = [])
    {
        $this->className = $className;
        $this->count = $count;
        $this->fieldDefinitionOverrides = $fieldDefinitionOverrides;
    }

    /**
     * @phpstan-return array<int, T>
     *
     * @psalm-return list<T>
     *
     * @return array<int, object>
     */
    public function resolve(Generator $faker, FixtureFactory $fixtureFactory): array
    {
        return $fixtureFactory->createMany(
            $this->className,
            $this->count,
            $this->fieldDefinitionOverrides
        );
    }
}
