<?php

declare(strict_types=1);

namespace Shared\DomainModel\Specification;

/**
 * Composite specification that allows combining multiple specifications.
 * Useful for building complex business rules from smaller, reusable components.
 *
 * @template T
 *
 * @extends AbstractSpecification<T>
 */
final readonly class CompositeSpecification extends AbstractSpecification
{
    /**
     * @var array<SpecificationInterface<T>>
     */
    private array $specifications;

    /**
     * @param array<SpecificationInterface<T>> $specifications
     */
    public function __construct(array $specifications = [])
    {
        $this->specifications = $specifications;
    }

    /**
     * Add a specification to the composite.
     *
     * @param SpecificationInterface<T> $specification
     *
     * @return self<T>
     */
    public function addSpecification(SpecificationInterface $specification): self
    {
        $specs = $this->specifications;
        $specs[] = $specification;

        return new self($specs);
    }

    /**
     * Checks if all specifications are satisfied by the candidate.
     * Acts as a logical AND between all specifications.
     *
     * @param T $candidate
     */
    public function isSatisfiedBy(mixed $candidate): bool
    {
        return array_all($this->specifications, fn ($specification) => $specification->isSatisfiedBy($candidate));
    }
}
