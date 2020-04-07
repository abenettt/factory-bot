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

use Doctrine\Common;
use Doctrine\ORM;

/**
 * Creates Doctrine entities for use in tests.
 *
 * See the README file for a tutorial.
 */
final class FixtureFactory
{
    private $entityManager;

    /**
     * @var array<string, EntityDefinition>
     */
    private $entityDefinitions = [];

    /**
     * @var bool
     */
    private $persist = false;

    public function __construct(ORM\EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Defines how to create a default entity of type `$className`.
     *
     * See the readme for a tutorial.
     *
     * @phpstan-param class-string<T> $className
     * @phpstan-template T
     *
     * @psalm-param class-string<T> $className
     * @psalm-param class-string<T> $className
     * @psalm-template T
     *
     * @param string                        $className
     * @param array<string, \Closure|mixed> $fieldDefinitions
     * @param \Closure                      $afterCreate
     *
     * @throws Exception\ClassMetadataNotFound
     * @throws Exception\ClassNotFound
     * @throws Exception\EntityDefinitionAlreadyRegistered
     * @throws Exception\InvalidFieldNames
     */
    public function defineEntity(string $className, array $fieldDefinitions = [], ?\Closure $afterCreate = null): void
    {
        if (\array_key_exists($className, $this->entityDefinitions)) {
            throw Exception\EntityDefinitionAlreadyRegistered::for($className);
        }

        if (!\class_exists($className, true)) {
            throw Exception\ClassNotFound::name($className);
        }

        try {
            $classMetadata = $this->entityManager->getClassMetadata($className);
        } catch (ORM\Mapping\MappingException $exception) {
            throw Exception\ClassMetadataNotFound::for($className);
        }

        /** @var array<int, string> $allFieldNames */
        $allFieldNames = \array_merge(
            \array_keys($classMetadata->fieldMappings),
            \array_keys($classMetadata->associationMappings),
            \array_keys($classMetadata->embeddedClasses)
        );

        $fieldNames = \array_filter($allFieldNames, static function (string $fieldName): bool {
            return false === \strpos($fieldName, '.');
        });

        /** @var array<int, string> $extraFieldNames */
        $extraFieldNames = \array_diff(
            \array_keys($fieldDefinitions),
            $fieldNames
        );

        if ([] !== $extraFieldNames) {
            throw Exception\InvalidFieldNames::notFoundIn(
                $classMetadata->getName(),
                ...$extraFieldNames
            );
        }

        $fieldDefinitions = \array_map(static function ($fieldDefinition): FieldDefinition\Resolvable {
            if ($fieldDefinition instanceof FieldDefinition\Resolvable) {
                return $fieldDefinition;
            }

            if ($fieldDefinition instanceof \Closure) {
                return FieldDefinition::sequence($fieldDefinition);
            }

            return FieldDefinition::value($fieldDefinition);
        }, $fieldDefinitions);

        $defaultEntity = $classMetadata->newInstance();

        foreach ($fieldNames as $fieldName) {
            if (\array_key_exists($fieldName, $fieldDefinitions)) {
                continue;
            }

            /** @var mixed $defaultFieldValue */
            $defaultFieldValue = $classMetadata->getFieldValue(
                $defaultEntity,
                $fieldName
            );

            $fieldDefinitions[$fieldName] = FieldDefinition::value($defaultFieldValue);
        }

        if (null === $afterCreate) {
            $afterCreate = static function (): void {
                // nothing to do here
            };
        }

        $this->entityDefinitions[$className] = new EntityDefinition(
            $classMetadata,
            $fieldDefinitions,
            $afterCreate
        );
    }

    /**
     * Get an entity and its dependencies.
     *
     * If you've called `persistOnGet()` then the entity is also persisted.
     *
     * @phpstan-param class-string<T> $className
     * @phpstan-return T
     * @phpstan-template T
     *
     * @psalm-param class-string<T> $className
     * @psalm-return T
     * @psalm-template T
     *
     * @param string               $className
     * @param array<string, mixed> $fieldOverrides
     *
     * @throws Exception\EntityDefinitionNotRegistered
     * @throws Exception\InvalidFieldNames
     *
     * @return object
     */
    public function get(string $className, array $fieldOverrides = [])
    {
        if (!\array_key_exists($className, $this->entityDefinitions)) {
            throw Exception\EntityDefinitionNotRegistered::for($className);
        }

        /** @var EntityDefinition $entityDefinition */
        $entityDefinition = $this->entityDefinitions[$className];

        $extraFieldNames = \array_diff(
            \array_keys($fieldOverrides),
            \array_keys($entityDefinition->fieldDefinitions())
        );

        if ([] !== $extraFieldNames) {
            throw Exception\InvalidFieldNames::notFoundIn(
                $entityDefinition->classMetadata()->getName(),
                ...$extraFieldNames
            );
        }

        /** @var ORM\Mapping\ClassMetadata $classMetadata */
        $classMetadata = $entityDefinition->classMetadata();

        /** @var T $entity */
        $entity = $classMetadata->newInstance();

        $fieldValues = [];

        foreach ($entityDefinition->fieldDefinitions() as $fieldName => $fieldDefinition) {
            if (\array_key_exists($fieldName, $fieldOverrides)) {
                $fieldValues[$fieldName] = $fieldOverrides[$fieldName];

                continue;
            }

            /** @var FieldDefinition $fieldDefinition */
            $fieldValues[$fieldName] = $fieldDefinition->resolve($this);
        }

        foreach ($fieldValues as $fieldName => $fieldValue) {
            $this->setField(
                $entity,
                $entityDefinition,
                $fieldName,
                $fieldValue
            );
        }

        $afterCreate = $entityDefinition->afterCreate();

        $afterCreate(
            $entity,
            $fieldValues
        );

        if ($this->persist && false === $classMetadata->isEmbeddedClass) {
            $this->entityManager->persist($entity);
        }

        return $entity;
    }

    /**
     * Get an array of entities and their dependencies.
     *
     * If you've called `persistOnGet()` then the entities are also persisted.
     *
     * @phpstan-param class-string<T> $className
     * @phpstan-return array<int, T>
     * @phpstan-template T
     *
     * @psalm-param class-string<T> $className
     * @psalm-return list<T>
     * @psalm-template T
     *
     * @param string               $className
     * @param array<string, mixed> $fieldOverrides
     * @param int                  $count
     *
     * @throws Exception\InvalidCount
     *
     * @return array<int, object>
     */
    public function getList(string $className, array $fieldOverrides = [], int $count = 1): array
    {
        $minimumCount = 1;

        if ($minimumCount > $count) {
            throw Exception\InvalidCount::notGreaterThanOrEqualTo(
                $minimumCount,
                $count
            );
        }

        $instances = [];

        for ($i = 0; $i < $count; ++$i) {
            $instances[] = $this->get(
                $className,
                $fieldOverrides
            );
        }

        return $instances;
    }

    /**
     * Sets whether `get()` should automatically persist the entity it creates.
     * By default it does not. In any case, you still need to call
     * flush() yourself.
     *
     * @param bool $enabled
     */
    public function persistOnGet(bool $enabled = true): void
    {
        $this->persist = $enabled;
    }

    /**
     * @return array<string, EntityDefinition>
     */
    public function definitions(): array
    {
        return $this->entityDefinitions;
    }

    /**
     * @param object           $entity
     * @param EntityDefinition $entityDefinition
     * @param string           $fieldName
     * @param mixed            $fieldValue
     */
    private function setField(object $entity, EntityDefinition $entityDefinition, string $fieldName, $fieldValue): void
    {
        $classMetadata = $entityDefinition->classMetadata();

        if ($classMetadata->isCollectionValuedAssociation($fieldName)) {
            $classMetadata->setFieldValue(
                $entity,
                $fieldName,
                self::createCollectionFrom($fieldValue)
            );
        } else {
            $classMetadata->setFieldValue(
                $entity,
                $fieldName,
                $fieldValue
            );

            if (\is_object($fieldValue) && $classMetadata->isSingleValuedAssociation($fieldName)) {
                $this->updateCollectionSideOfAssocation(
                    $entity,
                    $classMetadata,
                    $fieldName,
                    $fieldValue
                );
            }
        }
    }

    /**
     * @param mixed $value
     *
     * @return Common\Collections\ArrayCollection
     */
    private static function createCollectionFrom($value = []): Common\Collections\ArrayCollection
    {
        if (\is_array($value)) {
            return new Common\Collections\ArrayCollection($value);
        }

        return new Common\Collections\ArrayCollection();
    }

    private function updateCollectionSideOfAssocation(
        object $entity,
        ORM\Mapping\ClassMetadata $classMetadata,
        string $fieldName,
        object $fieldValue
    ): void {
        $association = $classMetadata->getAssociationMapping($fieldName);

        if (!\array_key_exists('inversedBy', $association)) {
            return;
        }

        $inversedBy = $association['inversedBy'];

        if (!\is_string($inversedBy) || '' === $inversedBy) {
            return;
        }

        $classMetadataOfFieldValue = $this->entityManager->getClassMetadata(\get_class($fieldValue));

        $collection = $classMetadataOfFieldValue->getFieldValue(
            $fieldValue,
            $inversedBy
        );

        if (!$collection instanceof Common\Collections\Collection) {
            return;
        }

        $collection->add($entity);
    }
}
