<?php

declare(strict_types=1);

namespace LM\WebFramework\Model\Type;

use LM\WebFramework\Model\Constraints\IConstraint;
use LM\WebFramework\Model\Constraints\INotNullConstraint;

/**
 * Parent interface for all type models.
 *
 * A type model is a class that model data. Instances provide constraints that
 * define what value is acceptable or not. The class name itself is a constraint
 * for the type that is allowed.
 *
 * This interface is implemented by all type models (either directly or through
 * another interface). This makes typing a type model possible (for instance, as
 * a function parameter).
 * All implementing, non-abstract classes are final. This ensures a type model
 * is exactly one (and not zero or more than one) type model. PHPStan ensures
 * classes are final.
 *
 * Using a separate class for each type makes it possible to type the functions
 * and to return constraints of the corresponding type only in getConstraints().
 *
 * Another way would be to only use the IScalarModel and the IEntity interfaces.
 * There would then be a ScalarModel class with a getType() method returning
 * a Type enum, reducing the number of classes and interfaces, and also
 * forcing one and only one type for each model. However, this would mean that
 * there wouldn’t be static typing for constraints.
 *
 * Using classes instead of, say, a method that returns an enum, will 1) make
 * it possible to add new classes without modifying said enum and 2) will make
 * sure a model cannot change its type and 3) provide typed constraints.
 *
 * @todo Type models can be seen as constraints, and it would be more consistent
 * to have IModel renamed to ITypeConstraint and implementing IConstraint.
 */
interface IModel extends IConstraint
{
    /**
     * @return ?INotNullConstraint Whether the content is necessarily specified
     * or can be left omitted.
     */
    public function getNotNullConstraint(): ?INotNullConstraint;

    /**
     * Shorter way to check whether the content can be null.
     */
    public function isNullable(): bool;
}
