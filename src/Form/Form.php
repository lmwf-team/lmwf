<?php

declare(strict_types=1);

namespace LM\WebFramework\Form;

use InvalidArgumentException;
use LM\WebFramework\Form\DataStructures\FormArray;
use LM\WebFramework\Form\DataStructures\StdFormData;

/**
 * Extracts a FormArray from HTTP requests, and converts arrays into FormArray-s.
 */
final class Form implements IFormExtractor
{
    private array $children;

    private ?string $ignoreValueOf;

    /**
     * @param IFormElement[] $children An array of Form Element constituting the form.
     * @param string|null $ignoreValueOf The name of a Form Element to ignore whose value it extracts should be ignored.
     * @throws \InvalidArgumentException If the given children are invalid.
     */
    public function __construct(
        array $children = [],
        ?string $ignoreValueOf = null,
    ) {
        $definedChildNames = [];
        foreach ($children as $c) {
            if (!($c instanceof IFormElement)) {
                throw new InvalidArgumentException('The form must be initialized with an array of IFormElement instances.');
            }
            if (in_array($c->getName(), $definedChildNames, true)) {
                throw new InvalidArgumentException('The form cannot have two direct children with identical names.');
            }
            $definedChildNames[] = $c->getName();
        }

        $this->children = $children;
        $this->ignoreValueOf = $ignoreValueOf;
    }

    public function extractFromRequest(array $requestParsedBody, ?array $uploadedFiles = null): FormArray {
        $formDatas = [];
        foreach ($this->children as $child) {
            if ($child->getName() === $this->ignoreValueOf) {
                $child->extractFromRequest($requestParsedBody, $uploadedFiles ?? []);
            } else {
                if ($child instanceof Form) {
                    
                }
                $formDatas[$child->getName()] = $child->extractFromRequest($requestParsedBody, $uploadedFiles ?? []);
            }
        }
        return new FormArray($formDatas);
    }
}