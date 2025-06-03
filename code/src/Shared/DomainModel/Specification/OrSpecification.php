<?php

declare(strict_types=1);

namespace Shared\DomainModel\Specification;

/**
 * Implementation of logical OR for specifications.
 * Satisfied if at least one of the conditions is satisfied.
 *
 * @template T
 *
 * @extends AbstractSpecification<T>
 */
final readonly class OrSpecification extends AbstractSpecification
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
     * Checks whether the candidate satisfies at least one of the specifications.
     *
     * @param T $candidate
     */
    public function isSatisfiedBy(mixed $candidate): bool
    {
        return $this->left->isSatisfiedBy($candidate) || $this->right->isSatisfiedBy($candidate);
    }
}
