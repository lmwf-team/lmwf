<?php

declare(strict_types=1);

namespace LM\WebFramework\Constraint\Type;

/**
 * Empty interface to easily check for a scalar type model.
 *
 * A scalar model is a model for which the allowed values are of a PHP scalar
 * type: int, string, null, float.
 */
interface IScalarModel extends IModel
{
}
