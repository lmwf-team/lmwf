<?php

declare(strict_types=1);

namespace LMWF\DataStructures\Factory;

use LMWF\DataStructures\AppList;
use LMWF\DataStructures\AppObject;

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
}
