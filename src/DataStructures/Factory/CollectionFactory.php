<?php

declare(strict_types=1);

namespace LMWF\DataStructures\Factory;

use LMWF\DataStructures\AppList;
use LMWF\DataStructures\AppObject;
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
     * @param array<string, mixed> $arrayEntity
     * @return AppObject<mixed>
     * @todo Delete this method, make AppObject (and AppList) handle it, this
     * would result in less imports (CollectionFactory), more predictable
     * (AppObjects and AppArrays never store arrays), stronger typing
     * (null|scalar|object instead of mixed).
     */
    public static function createDeepAppObject(array $arrayEntity): AppObject
    {
        $data = [];
        foreach ($arrayEntity as $property => $value) {
            if (is_array($value)) {
                if (array_is_list($value)) {
                    $data[$property] = self::createDeepAppList($value);
                } else {
                    // @todo Duplicate section of code with createDeepAppList
                    $onlyStringKeys = true;
                    foreach ($value as $key => $_) {
                        if (is_int($key)) {
                            $onlyStringKeys = false;
                            break;
                        }
                    }
                    if ($onlyStringKeys) {
                        // @phpstan-ignore argument.type
                        $data[$property] = self::createDeepAppObject($value);
                    } else {
                        $data[$property] = $value;
                    }
                }
            } else {
                $data[$property] = $value;
            }
        }
        return new AppObject($data);
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
