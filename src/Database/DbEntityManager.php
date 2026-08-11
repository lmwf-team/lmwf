<?php

declare(strict_types=1);

namespace LMWF\Database;

use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use LMWF\Database\Exceptions\InvalidDbDataException;
use LMWF\Database\Exceptions\NullDbDataNotAllowedException;
use LMWF\DataStructures\AppList;
use LMWF\DataStructures\AppObject;
use LMWF\DataStructures\Factory\CollectionFactory;
use LMWF\Constraint\Type\BoolModel;
use LMWF\Constraint\Type\DateTimeModel;
use LMWF\Constraint\Type\EntityModel;
use LMWF\Constraint\Type\ForeignEntityModel;
use LMWF\Constraint\Type\IntModel;
use LMWF\Constraint\Type\IScalarModel;
use LMWF\Constraint\Type\EntityListModel;
use LMWF\Constraint\Type\ListModel;
use LMWF\Constraint\Type\StringModel;
use UnexpectedValueException;

/**
 * @todo Could be renamed to DbEntityFactory / DbArrayFactory.

 * @phpstan-type dbscalar int|float|null|string
 * @phpstan-type dbrow array<string, dbscalar>
 */
final class DbEntityManager
{
    public const SEP = '_';

    /**
     * Converts array with App Data format into array ready to be passed to
     * PDO::execute.
     *
     * @todo Wait for PHPStan to support recursive types to define apparray type.
     * @todo Wait for PHPStan to understand that concatenation of non-decimal-int-string is non-decimal-int-string.
     *
     * @param array<string, mixed> $appArray
     * @param non-decimal-int-string $prefix
     * @param list<non-decimal-int-string> $propertiesToIgnore
     * @return dbrow
     */
    public function convertAppArrayIntoDbArray(
        iterable $appArray,
        string $prefix = '',
        array $propertiesToIgnore = [],
    ): array {
        $dbArray = [];
        foreach ($appArray as $pName => $pValue) {
            if (in_array($pName, $propertiesToIgnore, strict: true)) {
                continue;
            }
            if (is_array($pValue)) {
                if (array_is_list($pValue) && [] !== $pValue) {
                    throw new UnexpectedValueException('Cannot convert an app data list into DB data.');
                }

                // @phpstan-ignore argument.type, argument.type
                $dbArray += $this->convertAppArrayIntoDbArray($pValue, prefix: $prefix . $pName);
            } else {
                $dbArray[$prefix . $pName] = $this->convertAppVarToDbScalar($pValue);
            }
        }
        // @phpstan-ignore return.type
        return $dbArray;
    }

    /**
     * Converts a scalar under app data format into a scalar ready to be bound
     * to a PDOStatement parameter.
     *
     * For instance, it will convert boolens into 0 and 1.
     */
    public function convertAppVarToDbScalar(mixed $appVar): int|float|null|string
    {
        if (is_bool($appVar)) {
            return $appVar ? 1 : 0;
        } elseif ($appVar instanceof DateTimeInterface) {
            return $appVar->format('Y-m-d H:i:s');
        } elseif (is_int($appVar) || is_float($appVar) || is_null($appVar) || is_string($appVar)) {
            return $appVar;
        }
        throw new InvalidArgumentException("Could not convert App variable with type " . gettype($appVar) . " into DB scalar.");
    }

    /**
     * Convert a variable in DB Data format into App Data format.
     *
     * The following order of priority applies when converting the DB data into
     * app data: bool, int, DateTime, and finally string.
     *
     * @param dbscalar $dbData The data as returned by PDO.
     * @param IScalarModel $model The model of the DB Data.
     * @return mixed A PHP scalar type or base class object.
     * @throws InvalidArgumentException If $dbData is not of any DB Data variable type.
     */
    public function convertDbScalar(int|float|null|string $dbData, IScalarModel $model): mixed
    {
        if ($model instanceof BoolModel && (0 === $dbData || 1 === $dbData)) {
            return 1 === $dbData;
        } elseif ($model instanceof DateTimeModel && is_string($dbData)) {
            return new DateTimeImmutable($dbData);
        } elseif ($model instanceof IntModel && is_numeric($dbData)) {
            return intval($dbData);
        } elseif ($model instanceof StringModel && is_string($dbData)) {
            return $dbData;
        } elseif ($model->isNullable() && is_null($dbData)) {
            return null;
        } else {
            if (null === $dbData) {
                throw new NullDbDataNotAllowedException($dbData, $model);
            } else {
                throw new InvalidDbDataException($dbData, $model);
            }
        }
    }

    /**
     * Convert the result of a database query as a list of rows into an
     * AppObject.
     *
     * @todo Create type for dbRows, as a list of associative arrays?
     * @todo Throw exception is passed array is empty.
     * @param list<dbrow> $dbRows A list of associative arrays each storing a
     * different row.
     * @param EntityModel $model The model of each row.
     * @param int $index The row identifier of the main entity.
     * @return AppObject<mixed>
     */
    public function convertDbRowsToAppObject(array $dbRows, EntityModel $model, int $index = 0): AppObject
    {
        if (!array_is_list($dbRows)) {
            throw new InvalidArgumentException('$dbRows must be a list of rows.');
        }

        $transientAppObject = [];

        foreach ($model->getProperties() as $key => $property) {
            $value = null;

            if ($property instanceof ForeignEntityModel) {
                /**
                 * @todo If $property is nullable, don’t throw exception!
                 * @todo Create method to get referenceId.
                 * @todo Should return null?
                 */
                $referenceId = $dbRows[$index][$model->getIdentifier() . self::SEP . $property->getReferenceKeyInParent()];
                if (null !== $referenceId && !is_string($referenceId) && !is_int($referenceId)) {
                    throw new InvalidDbDataException($referenceId, $property, $property->getReferenceKeyInParent());
                }
                if (null !== $referenceId) {
                    $referencedRowNos = $this->getReferencedRowNos($dbRows, $property, $referenceId);
                    if (count($referencedRowNos) > 0) {
                        $value = $this->convertDbRowsToAppObject($dbRows, $property->getEntityModel(), $referencedRowNos[0]);
                    } elseif (!$property->isNullable()) {
                        throw new UnexpectedValueException("Could not find specifed foreign entity using reference ID {$referenceId} and reference key {$property->getReferenceKeyInParent()} in parent and {$property->getReferencedKeyInChild()} in child for property {$key}.");
                    }
                } elseif (!$property->isNullable()) {
                    // @todo Add test for this edge case.
                    throw new InvalidArgumentException('Mandatory sub entity reference id is null.');
                }
            } elseif ($property instanceof EntityModel) {
                $value = $this->convertDbRowsToAppObject($dbRows, $property, $index);
            } elseif ($property instanceof EntityListModel) {
                $itemModel = $property->getItemModel();
                $referenceId = $dbRows[$index][$model->getIdentifier() . self::SEP . $itemModel->getReferenceKeyInParent()];
                if (null !== $referenceId && !is_string($referenceId) && !is_int($referenceId)) {
                    throw new InvalidDbDataException($referenceId, $itemModel, $itemModel->getReferenceKeyInParent());
                }
                $value = $this->convertDbEntityList($dbRows, $property, $referenceId);
            } elseif ($property instanceof IScalarModel) {
                $value = $this->convertDbScalar($dbRows[$index][$model->getIdentifier() . self::SEP . $key], $property);
            } else {
                throw new UnexpectedValueException('Given property is not of any expected type.');
            }

            $transientAppObject[$key] = $value;
        }

        return CollectionFactory::createDeepAppObject($transientAppObject);
    }

    /**
     * @param list<dbrow> $dbRows
     * @return list<AppObject<mixed>>
     */
    public function convertDbEntityList(array $dbRows, EntityListModel $entityListModel, int|string|null $referenceId): array
    {
        $itemModel = $entityListModel->getItemModel();
        $appItems = [];
        $ids = [];

        foreach ($dbRows as $rowIndex => $row) {
            $rowReferenceId = $row[$itemModel->getEntityModel()->getIdentifier() . self::SEP . $itemModel->getReferencedKeyInChild()];
            $rowId = $row[$itemModel->getEntityModel()->getIdentifier() . self::SEP . $itemModel->getEntityModel()->getIdKey()];
            if ((null === $referenceId || $rowReferenceId === $referenceId) && !in_array($rowId, $ids, strict: true)) {
                $appItems[] = $this->convertDbRowsToAppObject($dbRows, $itemModel->getEntityModel(), $rowIndex);
                $ids[] = $rowId;
            }
        }

        return $appItems;
    }

    /**
     * For now, ListModel objects can only have an scalar item model. Hence
     * why the type of getItemModel() is not checked yet, look at commit
     * 6f25edc4af219c2f9753d9e4586f4dea843b4f70 to see how it was done.
     *
     * @param list<null|dbscalar> $dbDataList
     * @return list<mixed>
     */
    public function convertDbList(array $dbDataList, ListModel $listModel): array
    {
        $itemModel = $listModel->getItemModel();
        $appData = [];
        foreach ($dbDataList as $row) {
            $appData[] = $this->convertDbScalar($row, $itemModel);
        }
        return $appData;
    }

    /**
     * @param list<dbrow> $dbRows
     */
    public function convertDbRowsToEntityList(array $dbRows, EntityModel $itemModel): AppList
    {
        $appData = [];
        $ids = [];
        foreach ($dbRows as $rowNo => $row) {
            $rowEntityId = $row[$itemModel->getIdentifier() . self::SEP . $itemModel->getIdKey()];
            if (!in_array($rowEntityId, $ids, strict: true)) {
                $appData[] = $this->convertDbRowsToAppObject($dbRows, $itemModel, $rowNo);
                $ids[] = $rowEntityId;
            }
        }
        return new AppList($appData);
    }

    /**
     * Perform an outer join on two result sets, as if the two were issued from
     * an outer join request.
     *
     * @param list<array> $dbRowsLeft A list of rows returned from the database.
     * @param list<array> $dbRowsRight A list of rows returned from the database.
     * @return list<array> A list of rows with $dbRowsRight appended to $dbRowsLeft,
     * and with each row having all the same columns.
     */
    public function outerJoinDbRows(array $dbRowsLeft, array $dbRowsRight): array
    {
        if (0 === count($dbRowsLeft)) {
            return $dbRowsRight;
        } elseif (0 === count($dbRowsRight)) {
            return $dbRowsLeft;
        }

        $rowWithNullValues = array_map(fn () => null, $dbRowsLeft[0] + $dbRowsRight[0]);

        $dbRows = [];

        for ($i = 0; $i < max(count($dbRowsLeft), count($dbRowsRight)); $i++) {
            if ($i < count($dbRowsLeft)) {
                $dbRows[] = $dbRowsLeft[$i] + ($dbRowsRight[$i] ?? $rowWithNullValues);
            } else {
                $dbRows[] = array_merge($rowWithNullValues, $dbRowsRight[$i]);
            }
        }

        return $dbRows;
    }

    /**
     * @param array<string, mixed>[] $dbRows
     * @return list<int>
     */
    private function getReferencedRowNos(
        array $dbRows,
        ForeignEntityModel $property,
        int|string $referenceId,
    ): array {
        $prunedDbRows = array_filter(
            $dbRows,
            fn ($row) => $row[$property->getEntityModel()->getIdentifier() . self::SEP . $property->getReferencedKeyInChild()] === $referenceId,
        );

        return array_keys($prunedDbRows);
    }
}
