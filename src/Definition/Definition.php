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

namespace Ergebnis\FactoryBot\Definition;

use Ergebnis\FactoryBot\FixtureFactory;
use Faker\Generator;

interface Definition
{
    public function accept(FixtureFactory $fixturefactory, Generator $faker): void;
}
