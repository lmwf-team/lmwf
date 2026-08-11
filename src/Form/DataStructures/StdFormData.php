<?php

declare(strict_types=1);

namespace LM\WebFramework\Form\DataStructures;

/**
 * @todo Create a separate class for form array and form value?
 * @todo Remove unused methods?
 */
final class StdFormData implements IFormData
{
    public function __construct(
        private mixed $content,
        private array $errors = [],
    ) {
    }

    public function getContent(): mixed {
        return $this->content;
    }

    public function getData(?array $hereWeGo = null): mixed {
        if (is_array($this->content)) {
            $appArray = [];
            foreach ($hereWeGo ?? $this->content as $k => $v) {
                if ($v instanceof IFormData) {
                    $appArray[$k] = $v->getContent();
                } elseif (is_array($v)) {
                    $appArray[$k] = $this->getData($v);
                } else {
                    $appArray[$k] = $v;
                }
            }
            return $appArray;
        }
        return $this->content;
    }

    public function getArrayValue(): array {
        return $this->content;
    }

    public function getBoolValue(): ?bool {
        return is_bool($this->content) ? $this->content : null;
    }

    public function getChild(string $name): ?StdFormData {
        return $this->content[$name] ?? null;
    }

    public function getStringValue(): ?string {
        return is_string($this->content) ? $this->content : null;
    }

    public function getErrors(): array {
        return $this->errors;
    }

    public function getValue(): mixed {
        return $this->content;
    }

    public function hasErrors(): bool {
        return count($this->errors) > 0;
    }

    public function isNull(): bool {
        return null === $this->content;
    }
}