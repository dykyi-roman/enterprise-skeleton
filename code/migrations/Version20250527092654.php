<?php

declare(strict_types=1);

namespace migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250527092654 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create outbox_events table for Transactional Outbox Pattern';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE outbox_events (
            id UUID PRIMARY KEY,
            event_id UUID NOT NULL UNIQUE,
            event_type VARCHAR(255) NOT NULL,
            aggregate_id UUID NOT NULL,
            aggregate_type VARCHAR(255) NOT NULL,
            event_data JSONB NOT NULL,
            event_metadata JSONB,
            status VARCHAR(50) NOT NULL DEFAULT \'pending\',
            attempts INTEGER NOT NULL DEFAULT 0,
            max_attempts INTEGER NOT NULL DEFAULT 3,
            created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
            processed_at TIMESTAMP WITH TIME ZONE NULL,
            failed_at TIMESTAMP WITH TIME ZONE NULL,
            error_message TEXT NULL,
            next_retry_at TIMESTAMP WITH TIME ZONE NULL
        )');

        // Index for searching unprocessed events
        $this->addSql('CREATE INDEX idx_outbox_events_status ON outbox_events (status, created_at)');

        // Index for retry mechanism
        $this->addSql('CREATE INDEX idx_outbox_events_retry 
                       ON outbox_events (next_retry_at) 
                       WHERE status = \'failed\' AND attempts < max_attempts');

        // Index for searching by aggregate
        $this->addSql('CREATE INDEX idx_outbox_events_aggregate ON outbox_events (aggregate_id, aggregate_type)');

        // Index for cleaning old records
        $this->addSql('CREATE INDEX idx_outbox_events_processed_at 
                       ON outbox_events (processed_at) 
                       WHERE status = \'processed\'');

        // Creating ENUM for statuses (alternative approach)
        $this->addSql('ALTER TABLE outbox_events 
                       ADD CONSTRAINT chk_outbox_events_status 
                       CHECK (status IN (\'pending\', \'processing\', \'processed\', \'failed\', \'dead_letter\'))');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE outbox_events');
    }
}
