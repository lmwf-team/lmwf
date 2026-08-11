<?php

declare(strict_types=1);

namespace LMWF\DataStructures\Factory;

use LMWF\DataStructures\AppList;
use LMWF\DataStructures\AppObject;
use UnexpectedValueException;

class CollectionFactory
{
    /**
     * @param list<array<mixed>> $list
     */
    public static function createDeepAppList(array $list): AppList
    {
        $data = [];
        foreach ($list as $row) {
            if (is_array($row)) {
                if (array_is_list($row)) {
                    $data[] = self::createDeepAppList($row);
                } else {
                    $data[] = self::createDeepAppObject($row);
                }
            } else {
                $data[] = $row;
            }
        }

        return new AppList($data);
    }

    /**
     * @param array<string, mixed> $arrayEntity
     */
    public static function createDeepAppObject(array $arrayEntity): AppObject
    {

        $data = [];
        foreach ($arrayEntity as $property => $value) {
            if (is_array($value)) {
                if (array_is_list($value)) {
                    $data[$property] = self::createDeepAppList($value);
                } else {
                    $data[$property] = self::createDeepAppObject($value);
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
     * @todo Return AppObject instead?
     * @return array<string, mixed>
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
        return $decoded;
    }
}
