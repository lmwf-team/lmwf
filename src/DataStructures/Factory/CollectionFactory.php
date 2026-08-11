<?php

declare(strict_types=1);

namespace LM\WebFramework\DataStructures\Factory;

use LM\WebFramework\DataStructures\AppList;
use LM\WebFramework\DataStructures\AppObject;

class CollectionFactory
{
    /**
     * @param list<mixed> $list
     */
    public static function createDeepAppList(array $list): AppList
    {
        return new AppList(self::convertProperties($list));
    }

    /**
     * @param array<non-decimal-int-string, mixed> $object
     */
    public static function createDeepAppObject(array $object): AppObject
    {
        return new AppObject(self::convertProperties($object));
    }

    /**
     * Used by the class to convert properties into objects of corresponding
     * DataStructures class.
     * @param mixed[] $array
     * @return array<AppList|AppObject>
     */
    private static function convertProperties(array $array): array
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (array_is_list($value)) {
                    $array[$key] = self::createDeepAppList($value);
                } else {
                    $array[$key] = self::createDeepAppObject($value);
                }
            }
        }

        return $array;
    }
}
