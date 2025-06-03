<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250527092424 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create event_store table for Event Sourcing';
    }

    public function up(Schema $schema): void
    {
        // Drop old table if exists
        $this->addSql('DROP TABLE IF EXISTS event_store');

        $this->addSql(
            'CREATE TABLE event_store (
            id UUID PRIMARY KEY,
            event_id VARCHAR(36) NOT NULL,
            aggregate_id VARCHAR(36) NOT NULL,
            event_type VARCHAR(255) NOT NULL,
            payload TEXT NOT NULL,
            version INTEGER NOT NULL,
            occurred_at TIMESTAMP WITH TIME ZONE NOT NULL,
            created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW()
        )'
        );

        // Unique index to prevent duplicate events for one version of an aggregate
        $this->addSql('CREATE UNIQUE INDEX idx_event_store_aggregate_version ON event_store (aggregate_id, version)');

        // Index for quick search of events by aggregate
        $this->addSql('CREATE INDEX idx_event_store_aggregate ON event_store (aggregate_id, aggregate_type)');

        // Index for searching by event type
        $this->addSql('CREATE INDEX idx_event_store_event_type ON event_store (event_type)');

        // Index for temporary queries
        $this->addSql('CREATE INDEX idx_event_store_occurred_at ON event_store (occurred_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE event_store');
    }
}
