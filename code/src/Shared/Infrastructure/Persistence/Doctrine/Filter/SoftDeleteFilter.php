<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Persistence\Doctrine\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

/**
 * @see Use in config/packages/doctrine.yaml
 */
class SoftDeleteFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, string $targetTableAlias): string
    {
        if (!$targetEntity->hasField('deletedAt')) {
            return ''; // not use filter
        }

        return sprintf('%s.deleted_at IS NULL', $targetTableAlias);
    }
}