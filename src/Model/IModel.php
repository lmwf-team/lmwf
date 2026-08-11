<?php

declare(strict_types=1);

namespace LM\WebFramework\Model;

/**
 * Provides a definition for elements of a certain data type, either structured
 * or unstructured.
 * 
 * I chose to use interfaces to differenciate the different underlying types.
 * It also makes testing for the model more readable (instanceof instead of
 * null !== $model->getArrayDefinition()). However, this might be bad OOP
 * practice?
 * 
 * Each type has its own interface. This makes it possible to type the list of
 * constraints. Thus, each model defines the give, and its constraints is a list
 * of constrainst that check variables of the given type.
 * 
 * Another way would be to only use the IScalarModel and the IEntity interfaces.
 * There would then be a ScalarModel class with a getType() method returning
 * a Type enum, reducing the number of classes and interfaces, and more
 * importantly forcing one and only one type for each model. However, this
 * would mean that there wouldn’t be static typing for constraints.
 * 
 * Actually, I will use final classes to identify each type. Because of PHP
 * single inheritance, this will make sure a model is necessarily one and only
 * one type. This will also reduce code duplication and the number of files by
 * getting rides of interfaces, which are probably useless and often empty in
 * this case.
 * 
 * Using classes instead of, say, a method that returns an enum, will 1) make
 * it possible to add new classes without modifying said enum and 2) will make
 * sure a model cannot change its type.
 * 
 * Constraints are separated from the model for one reason: the model itself is
 * specifies the functional type (i.e. the PHP variable type or class in the
 * case of PHP) to convert data into, while constraints apply on the transformed
 * type. They thus only intervene after.
 */
interface IModel
{
    /**
     * @return bool Whether the content is necessarily specified or can be left
     * omitted.
     */
    public function isNullable(): bool;
}