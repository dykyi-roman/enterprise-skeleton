<?php

declare(strict_types=1);

namespace Shared\DomainModel\Specification;

/**
 * Implementation of logical AND with NOT for specifications.
 * Satisfied if the left condition is satisfied AND the right condition is NOT satisfied.
 *
 * @template T
 *
 * @extends AbstractSpecification<T>
 */
final readonly class AndNotSpecification extends AbstractSpecification
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
     * Checks if the candidate meets the left specification
     * AND does NOT meet the right specification.
     *
     * @param T $candidate
     */
    public function isSatisfiedBy(mixed $candidate): bool
    {
        return $this->left->isSatisfiedBy($candidate) && !$this->right->isSatisfiedBy($candidate);
    }
}
