<?php

declare(strict_types=1);

namespace Shared\DomainModel\Specification;

/**
 * Interface for the Specification pattern.
 * Specifications allow encapsulation of business rules that can be combined using logical operators.
 *
 * @template T
 */
interface SpecificationInterface
{
    /**
     * Check if the candidate satisfies the specification.
     *
     * @param T $candidate The object to check against the specification
     */
    public function isSatisfiedBy(mixed $candidate): bool;

    /**
     * Combine this specification with another using AND.
     *
     * @param SpecificationInterface<T> $other
     *
     * @return SpecificationInterface<T>
     */
    public function and(self $other): self;

    /**
     * Combine this specification with another using OR.
     *
     * @param SpecificationInterface<T> $other
     *
     * @return SpecificationInterface<T>
     */
    public function or(self $other): self;

    /**
     * Negate this specification (NOT).
     *
     * @return SpecificationInterface<T>
     */
    public function not(): self;
}
