<?php

declare(strict_types=1);

namespace LM\WebFramework\Form\DataStructures;

/*
* @todo Remove unused methods?
*/
final class FormArray implements IFormData
{
    /**
     * @param array<IFormData> $submissions An array of submissions, indexed by the name of the form element that extracted it.
     */
    public function __construct(
        private array $submissions,
    ) {
    }

    public function getContent(): array {
        $data = [];
        foreach ($this->submissions as $key => $s) {
            $data[$key] = $s->getContent();
        }

        return $data;
    }

    public function getChild(string $name): ?IFormData {
        return $this->submissions[$name] ?? null;
    }

    /**
     * @return array<IFormData>
     */
    public function getChildren(): array {
        return $this->submissions;
    }

    public function getErrors(): array {
        $errors = [];
        foreach ($this->submissions as $key => $formValue) {
            $errors[$key] = $formValue->getErrors();
        }
        return $errors;
    }

    public function getValue(): mixed {
        return $this->submissions;
    }

    public function hasErrors(): bool {
        foreach ($this->submissions as $formData) {
            if ($formData->hasErrors()) {
                return true;
            }
        }
        return false;
    }
}