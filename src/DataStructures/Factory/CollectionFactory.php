<?php

namespace LM\WebFramework\DataStructures\Factory;

use LM\WebFramework\DataStructures\AppList;
use LM\WebFramework\DataStructures\AppObject;

class CollectionFactory
{
    public function createDeepAppList(array $list): AppList
    {
        return new AppList($this->convertProperties($list));
    }

    public function createDeepAppObject(array $object): AppObject
    {
        return new AppObject($this->convertProperties($object));
    }

    /**
     * Used by the class to convert properties into objects of corresponding
     * DataStructures class.
     */
    private function convertProperties(array $array): array
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (array_is_list($value)) {
                    $array[$key] = $this->createDeepAppList($value);
                } else {
                    $array[$key] = $this->createDeepAppObject($value);
                }
            }
        }

        return $array;
    }
}