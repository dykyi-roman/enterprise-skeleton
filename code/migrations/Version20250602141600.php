<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250602141600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create outbox_events table for current OutboxEvent class structure';
    }

    public function up(Schema $schema): void
    {
        // Drop old table if exists
        $this->addSql('DROP TABLE IF EXISTS outbox_events');
        
        // Create new table matching current OutboxEvent structure
        $this->addSql('CREATE TABLE outbox_events (
            id VARCHAR(36) PRIMARY KEY,
            event_id VARCHAR(36) NOT NULL,
            event_type VARCHAR(255) NOT NULL,
            aggregate_id VARCHAR(36) NOT NULL,
            payload TEXT NOT NULL,
            created_at TIMESTAMP WITH TIME ZONE NOT NULL,
            processed_at TIMESTAMP WITH TIME ZONE NULL,
            is_processed BOOLEAN NOT NULL DEFAULT FALSE,
            retry_count INTEGER NOT NULL DEFAULT 0,
            error TEXT NULL
        )');

        // Index for searching unprocessed events
        $this->addSql('CREATE INDEX idx_outbox_events_is_processed ON outbox_events (is_processed)');
        
        // Index for searching by event_id
        $this->addSql('CREATE UNIQUE INDEX idx_outbox_events_event_id ON outbox_events (event_id)');
        
        // Index for searching by aggregate
        $this->addSql('CREATE INDEX idx_outbox_events_aggregate_id ON outbox_events (aggregate_id)');
        
        // Index for sorting by creation date (for FIFO processing)
        $this->addSql('CREATE INDEX idx_outbox_events_created_at ON outbox_events (created_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE outbox_events');
    }
}
