<?php

declare(strict_types=1);

namespace Shared\DomainModel\Specification;

/**
 * Implementation of logical NOT for specifications.
 * Satisfied if the original condition is not satisfied.
 *
 * @template T
 *
 * @extends AbstractSpecification<T>
 */
final readonly class NotSpecification extends AbstractSpecification
{
    /**
     * @param SpecificationInterface<T> $specification
     */
    public function __construct(
        private SpecificationInterface $specification,
    ) {
    }

    /**
     * Checks that the candidate does NOT meet the specification.
     *
     * @param T $candidate
     */
    public function isSatisfiedBy(mixed $candidate): bool
    {
        return !$this->specification->isSatisfiedBy($candidate);
    }
}
