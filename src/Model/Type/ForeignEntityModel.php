<?php

declare(strict_types=1);

namespace LM\WebFramework\Model\Type;

final class ForeignEntityModel extends AbstractModel
{
    public function __construct(
        private EntityModel $entityModel,
        private string $referenceKeyInChild,
        private string $referenceKeyInParent,
        bool $isNullable = false,
    ) {
        // @todo isNullable should be extracted from entityModel?
        parent::__construct($isNullable);
    }

    public function getEntityModel(): EntityModel
    {
        return $this->entityModel;
    }

    public function getReferencedKeyInChild(): string
    {
        return $this->referenceKeyInChild;
    }

    public function getReferenceKeyInParent(): string
    {
        return $this->referenceKeyInParent;
    }
}