<?php

declare(strict_types=1);

namespace LMWF\Constraint\Type;

/**
 * Empty interface to easily check for a scalar type model.
 *
 * A scalar model defines a set of values without relying on submodels (e.g. as
 * opposed to a list model whose allowed values are defined by its item model).
 */
interface IScalarModel extends IModel
{
}
