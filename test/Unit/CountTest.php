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

namespace Ergebnis\FactoryBot\Test\Unit;

use Ergebnis\FactoryBot\Count;
use Ergebnis\FactoryBot\Exception;
use Ergebnis\Test\Util\Helper;
use PHPUnit\Framework;

/**
 * @internal
 *
 * @covers \Ergebnis\FactoryBot\Count
 *
 * @uses \Ergebnis\FactoryBot\Exception\InvalidMaximum
 * @uses \Ergebnis\FactoryBot\Exception\InvalidMinimum
 * @uses \Ergebnis\FactoryBot\Exception\InvalidCount
 */
final class CountTest extends Framework\TestCase
{
    use Helper;

    /**
     * @dataProvider \Ergebnis\FactoryBot\Test\DataProvider\IntProvider::lessThanZero()
     *
     * @param int $value
     */
    public function testExactRejectsValueLessThanZero(int $value): void
    {
        $this->expectException(Exception\InvalidCount::class);
        $this->expectExceptionMessage(\sprintf(
            'Count needs to be greater than or equal to 0, but %d is not.',
            $value
        ));

        Count::exact($value);
    }

    /**
     * @dataProvider \Ergebnis\FactoryBot\Test\DataProvider\IntProvider::greaterThanOrEqualToZero()
     *
     * @param int $value
     */
    public function testExactReturnsCountWhenValueIsGreaterThanZero(int $value): void
    {
        $count = Count::exact($value);

        self::assertInstanceOf(Count::class, $count);
        self::assertSame($value, $count->minimum());
        self::assertSame($value, $count->maximum());
    }

    /**
     * @dataProvider \Ergebnis\FactoryBot\Test\DataProvider\IntProvider::lessThanZero()
     *
     * @param int $minimum
     */
    public function testBetweenRejectsMinimumLessThanZero(int $minimum): void
    {
        $maximum = $minimum + 1;

        $this->expectException(Exception\InvalidMinimum::class);
        $this->expectExceptionMessage(\sprintf(
            'Minimum needs to be greater than or equal to 0, but %d is not.',
            $minimum
        ));

        Count::between(
            $minimum,
            $maximum
        );
    }

    /**
     * @dataProvider \Ergebnis\FactoryBot\Test\DataProvider\IntProvider::greaterThanOrEqualToZero()
     *
     * @param int $difference
     */
    public function testBetweenRejectsMaximumNotGreaterThanMinimum(int $difference): void
    {
        $minimum = self::faker()->numberBetween(1);
        $maximum = $minimum - $difference;

        $this->expectException(Exception\InvalidMaximum::class);
        $this->expectExceptionMessage(\sprintf(
            'Maximum needs to be greater than minimum %d, but %d is not.',
            $minimum,
            $maximum
        ));

        Count::between(
            $minimum,
            $maximum
        );
    }

    /**
     * @dataProvider \Ergebnis\FactoryBot\Test\DataProvider\IntProvider::greaterThanOrEqualToZero()
     *
     * @param int $minimum
     */
    public function testBetweenReturnsCountWhenMinimumIsGreaterThanOrEqualToZeroAndMaximumIsGreaterThanMinimum(int $minimum): void
    {
        $faker = self::faker();

        $maximum = $minimum + $faker->numberBetween(1);

        $count = Count::between(
            $minimum,
            $maximum
        );

        self::assertInstanceOf(Count::class, $count);
        self::assertSame($minimum, $count->minimum());
        self::assertSame($maximum, $count->maximum());
    }
}
