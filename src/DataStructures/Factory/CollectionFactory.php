<?php

declare(strict_types=1);

namespace LMWF\DataStructures\Factory;

use LMWF\DataStructures\AppList;
use LMWF\DataStructures\AppPosIntArray;
use LMWF\DataStructures\AppObject;
use LMWF\DataStructures\ImmutableArray;
use UnexpectedValueException;

class CollectionFactory
{
    /**
     * @param list<mixed> $list
     */
    public static function createDeepAppList(array $list): AppList
    {
        $data = [];
        foreach ($list as $item) {
            if (is_array($item)) {
                if (array_is_list($item)) {
                    $data[] = self::createDeepAppList($item);
                } else {
                    $onlyStringKeys = true;
                    foreach ($item as $key => $_) {
                        if (is_int($key)) {
                            $onlyStringKeys = false;
                            break;
                        }
                    }
                    if ($onlyStringKeys) {
                        // @phpstan-ignore argument.type
                        $data[] = self::createDeepAppObject($item);
                    }
                }
            } else {
                $data[] = $item;
            }
        }

        return new AppList($data);
    }

    /**
     * @param array<string, mixed> $dataRaw
     * @return AppObject<mixed>
     * @todo Delete this method, make AppObject (and AppList) handle it, this
     * would result in less imports (CollectionFactory), more predictable
     * (AppObjects and AppArrays never store arrays), stronger typing
     * (null|scalar|object instead of mixed).
     */
    public static function createDeepAppObject(array $dataRaw): AppObject
    {
        return self::createDeepImmutableArray($dataRaw, AppObject::class);
    }

    /**
     * @template T of AppObject|AppPosIntArray
     * @param array<string, mixed> $dataRaw
     * @param class-string<T> $class
     * @return T
     */
    private static function createDeepImmutableArray(array $dataRaw, string $class): ImmutableArray
    {
        $data = [];
        foreach ($dataRaw as $key => $value) {
            if (is_array($value)) {
                if (array_is_list($value)) {
                    $data[$key] = self::createDeepImmutableArray($value, AppList::class);
                } else {
                    // @todo Duplicate section of code with createDeepAppList
                    $onlyStringKeys = true;
                    $onlyIntKeys = true;
                    foreach ($value as $subKey => $_) {
                        if (is_int($subKey)) {
                            $onlyStringKeys = false;
                        } elseif (is_string($subKey)) {
                            $onlyIntKeys = false;
                        }
                    }
                    if ($onlyStringKeys) {
                        // @phpstan-ignore argument.type
                        $data[$key] = self::createDeepImmutableArray($value, AppObject::class);
                    } elseif ($onlyIntKeys) {
                        $data[$key] = self::createDeepImmutableArray($value, AppPosIntArray::class);
                    } else {
                        $data[$key] = $value;
                    }
                }
            } else {
                $data[$key] = $value;
            }
        }
        return match ($class) {
            AppObject::class => new AppObject($data),
            AppPosIntArray::class => new AppPosIntArray($data),
            AppList::class => new AppList($data),
            default => throw new UnexpectedValueException("Did not recognise provided collection class: '$class'.")
        };
    }

    /**
     * Parse the given JSON file as an associative array.
     *
     * @param string $filePath Path to the JSON file.
     * @return array<string, mixed>
     * @todo Return AppObject instead?
     * @todo Wait for PHPStan to fix issue and remove ignore of return.type.
     */
    public static function fromJson(string $filePath): array
    {
        $fileContent = file_get_contents($filePath);
        if (false === $fileContent) {
            throw new UnexpectedValueException("Could not read content of file '$filePath'.");
        }
        $decoded = json_decode($fileContent, associative: true, flags: JSON_THROW_ON_ERROR);
        if (!is_array($decoded)) {
            throw new UnexpectedValueException("Expected the decoded JSON to be an associative array.");
        }

        if (!array_all(array_keys($decoded), fn ($value, $_) => is_string($value))) {
            throw new UnexpectedValueException('Not all of the keys of the parsed JSON were strings.');
        }
        // @phpstan-ignore return.type
        return $decoded;
    }
}
