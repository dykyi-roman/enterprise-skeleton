<?php

declare(strict_types=1);

namespace Shared\DomainModel\Specification;

/**
 * @template T
 * @implements SpecificationInterface<T>
 */
abstract readonly class AbstractSpecification implements SpecificationInterface
{
    /**
     * @inheritDoc
     */
    public function and(SpecificationInterface $other): SpecificationInterface
    {
        return new AndSpecification($this, $other);
    }

    /**
     * @inheritDoc
     */
    public function or(SpecificationInterface $other): SpecificationInterface
    {
        return new OrSpecification($this, $other);
    }

    /**
     * @inheritDoc
     */
    public function not(): SpecificationInterface
    {
        return new NotSpecification($this);
    }
}
