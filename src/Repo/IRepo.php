<?php

declare(strict_types=1);

namespace LMWF\Repo;

use LMWF\DataStructures\AppObject;
use LMWF\Constraint\Type\IModel;

/**
 * Retrieves persisted entities, with each implementation responsible for a
 * specific entity type.
 *
 * Persisted means existing outside a PHP process or any form of caching,
 * including ACPU.
 *
 * Entities are data structured as arrays with snake-cased string keys,
 * including a key whose value is a string (or an int cast to a string) that
 * serves as an identifier, distinguishing a particular entity from others of
 * the same type.
 *
 * @todo Add findAll, findOne?
 */
interface IRepo
{
    /**
     * @param string $id The ID of the entity to retrieve.
     * @param ?IModel $overrideModel If not null, override the model used to
     * extract the app data from the query's results.
     * @return ?AppObject<mixed>
     */
    public function find(string $id, ?IModel $overrideModel = null): ?AppObject;
}
