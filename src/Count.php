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

namespace Ergebnis\FactoryBot;

final class Count
{
    /**
     * @var int
     */
    private $value;

    /**
     * @param int $value
     *
     * @throws Exception\InvalidCount
     */
    public function __construct(int $value)
    {
        if (1 > $value) {
            throw Exception\InvalidCount::notGreaterThanOrEqualTo(
                1,
                $value
            );
        }

        $this->value = $value;
    }

    public function value(): int
    {
        return $this->value;
    }
}