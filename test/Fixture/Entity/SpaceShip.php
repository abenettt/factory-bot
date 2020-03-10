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

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity
 */
class SpaceShip
{
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;

    /**
     * @Column
     */
    protected $name;

    /**
     * @OneToMany(targetEntity="Person", mappedBy="spaceShip")
     */
    protected $crew;

    /**
     * @var bool
     */
    protected $constructorWasCalled = false;

    public function __construct($name)
    {
        $this->name = $name;
        $this->crew = new ArrayCollection();
        $this->constructorWasCalled = true;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function getCrew()
    {
        return $this->crew;
    }

    public function constructorWasCalled()
    {
        return $this->constructorWasCalled;
    }
}