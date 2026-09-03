<?php

declare(strict_types=1);

namespace LMWF\Tests\Mocks;

use LMWF\Constraint\Type\IModel;
use LMWF\DataStructures\AppObject;
use LMWF\Repo\IRepo;

final class UserRepo implements IRepo
{
    const array DB_DATA = [
        self::USER_ID => [
            'name' => self::USER_NAME,
            'id' => self::USER_ID,
            'a' => self::USER_A,
        ],
    ];
    const string USER_A = 'One letter!';
    const string USER_ID = 'jd';
    const string USER_NAME = 'John Doe';

    /**
     * @return AppObject<string>
     */
    #[\Override]
    public function find(string $id, ?IModel $overrideModel = null): ?AppObject
    {
        if (key_exists($id, self::DB_DATA)) {
            return new AppObject([
                'name' => self::DB_DATA[$id]['name'],
                'id' => self::DB_DATA[$id]['id'],
                'a' => self::DB_DATA[$id]['a'],
            ]);
        }
        return null;
    }
}
