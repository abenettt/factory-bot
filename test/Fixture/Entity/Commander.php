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

namespace Ergebnis\FactoryBot\Test\Fixture\Entity;

/**
 * @Entity
 */
class Commander
{
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(
     *     name="id",
     *     type="integer"
     * )
     *
     * @var string
     */
    private $id;

    /**
     * @Embedded(
     *     class="Ergebnis\FactoryBot\Test\Fixture\Entity\Name",
     *     columnPrefix=false
     * )
     *
     * @var Name
     */
    private $name;

    public function __construct()
    {
        $this->name = new Name();
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): Name
    {
        return $this->name;
    }
}