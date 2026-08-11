<?php

declare(strict_types=1);

namespace LM\WebFramework\Type;

use DateTimeInterface;
use DomainException;
use InvalidArgumentException;
use LM\WebFramework\Constraints\INotNullConstraint;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\DataStructures\ConstraintViolation;
use LM\WebFramework\Model\IBoolModel;
use LM\WebFramework\Model\IDateTimeModel;
use LM\WebFramework\Model\IEntity;
use LM\WebFramework\Model\IModel;
use LM\WebFramework\Model\IScalar;
use LM\WebFramework\Model\IScalarModel;
use LM\WebFramework\Validator\ValidatorFactory;

final class ModelValidator
{
    public function __construct(
        private ValidatorFactory $validatorFactory,
    ) {
    }

    public function validate(mixed $data, IModel $model): array
    {
        if (null === $data) {
            if (!$model->isNullable()) {
                return [
                    new ConstraintViolation(
                        new class implements INotNullConstraint {},
                        'Data is not allowed to be null.',
                    ),
                ];
            } else {
                return [];
            }
        }

        if ($model instanceof IEntity && $data instanceof AppObject) {
            return $this->validateEntity($data, $model);
        }
        if ($model instanceof IBoolModel) {

        }
        if ($model instanceof IDateTimeModel) {

        }
        
        if ($model instanceof IScalarModel && is_scalar($data)) {
            return $this->validateScalar($data, $model);
        }

        throw new InvalidArgumentException("Data is not of any type supported by the given model.");
    }

    /**
     * @throws InvalidArgumentException If $data is not of the expected class or type.
     * @throws DomainException If $model is unknown.
     */
    private function validateEntity(AppObject $entity, IEntity $model): array
    {
        $properties = $model->getProperties();
        $constraintViolations = [];
        if (count($properties) !== count($entity)) {
            throw new InvalidArgumentException('The provided array does not have the expected number of properties.');
        }

        foreach ($properties as $key => $property) {
            $violations = $this->validate($entity[$key], $property);
            if (count($violations) > 0) {
                $constraintViolations[$key] = $violations;
            }
        }
        return $constraintViolations;
        // } elseif (null !== $listNodeModel) {
        //     $constraintViolations = [];
        //     foreach ($data as $key => $value) {
        //         $violations = $this->validate($value, $listNodeModel);
        //         if (count($violations) > 0) {
        //             $constraintViolations[$key] = key_exists($key, $constraintViolations) ? array_merge_recursive($constraintViolations[$key], $violations) : $violations;
        //         }
        //     }
        //     return $constraintViolations;
        // }
    }

    /**
     * @throws InvalidArgumentException If $data is not of the expected class or type.
     * @throws DomainException If $model is unknown.
     */
    private function validateScalar(bool|int|string|DateTimeInterface $data, IScalarModel $model): array {        
        if (is_string($data)) {
            $stringConstraints = $model->getStringConstraints();
            if (null !== $stringConstraints) {
                $constraintViolations = [];
                foreach ($stringConstraints as $c) {
                    $violations = $this->validatorFactory->createValidator($c, $this)->validate($data);
                    if (count($violations) > 0) {
                        $constraintViolations = array_merge_recursive($constraintViolations, $violations);
                    }
                }
                return $constraintViolations;
            }
        }
        if (is_numeric($data)) {
            $numericConstraints = $model->getIntegerConstraints();
            if (null !== $numericConstraints) {
                $constraintViolations = [];
                foreach ($numericConstraints as $c) {
                    $violations = $this->validatorFactory->createValidator($c, $this)->validate($data);
                    if (count($violations) > 0) {
                        $constraintViolations = array_merge_recursive($constraintViolations, $violations);
                    }
                }
                return $constraintViolations;
            }
        }
        if ($data instanceof DateTimeInterface) {
            $constraints = $model->getDateTimeConstraints();
            if (null !== $constraints) {
                $constraintViolations = [];
                foreach ($constraints as $c) {
                    $violations = $this->validatorFactory->createValidator($c, $this)->validate($data);
                    if (count($violations) > 0) {
                        $constraintViolations = array_merge_recursive($constraintViolations, $violations);
                    }
                }
                return $constraintViolations;
            }
        }
        if (is_bool($data)) {
            if ($model->isBool()) {
                return [];
            }
        }

        throw new InvalidArgumentException("Data is not of any type supported by the given model.");
    }
}