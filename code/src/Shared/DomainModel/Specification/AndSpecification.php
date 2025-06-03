<?php

declare(strict_types=1);

namespace Shared\DomainModel\Specification;

/**
 * Implementation of logical AND for specifications.
 * Satisfied if both conditions are satisfied.
 *
 * @template T
 *
 * @extends AbstractSpecification<T>
 */
final readonly class AndSpecification extends AbstractSpecification
{
    /**
     * @param SpecificationInterface<T> $left
     * @param SpecificationInterface<T> $right
     */
    public function __construct(
        private SpecificationInterface $left,
        private SpecificationInterface $right,
    ) {
    }

    /**
     * Checks if the candidate meets both specifications.
     *
     * @param T $candidate
     */
    public function isSatisfiedBy(mixed $candidate): bool
    {
        return $this->left->isSatisfiedBy($candidate) && $this->right->isSatisfiedBy($candidate);
    }
}
