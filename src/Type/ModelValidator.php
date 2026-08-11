<?php

namespace LM\WebFramework\Type;

use DateTimeInterface;
use DomainException;
use InvalidArgumentException;
use LM\WebFramework\Constraints\INotNullConstraint;
use LM\WebFramework\DataStructures\ConstraintViolation;
use LM\WebFramework\Model\IModel;
use LM\WebFramework\Validator\ValidatorFactory;

class ModelValidator
{
    public function __construct(
        private ValidatorFactory $validatorFactory,
    ) {
    }

    /**
     * @throws InvalidArgumentException If $data is not of the expected class or type.
     * @throws DomainException If $model is unknown.
     */
    public function validate(mixed $data, IModel $model): array {        
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
        if (is_array($data)) {
            $arrayDefinition = $model->getArrayDefinition();
            $listNodeModel = $model->getListNodeModel();
            if (null !== $arrayDefinition) {
                $constraintViolations = [];
                if (count($arrayDefinition) !== count($data)) {
                    throw new InvalidArgumentException('The provided array does not have the expected number of properties.');
                }
                foreach ($arrayDefinition as $key => $property) {
                    $violations = $this->validate($data[$key], $property);
                    if (count($violations) > 0) {
                        $constraintViolations[$key] = $violations;
                    }
                }
                return $constraintViolations;
            } elseif (null !== $listNodeModel) {
                $constraintViolations = [];
                foreach ($data as $key => $value) {
                    $violations = $this->validate($value, $listNodeModel);
                    if (count($violations) > 0) {
                        $constraintViolations[$key] = key_exists($key, $constraintViolations) ? array_merge_recursive($constraintViolations[$key], $violations) : $violations;
                    }
                }
                return $constraintViolations;
            }
        }
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