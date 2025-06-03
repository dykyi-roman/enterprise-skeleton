# Shared application components

This directory contains shared components used across all application contexts.

## Rate Limiting

A mechanism for request rate limiting to protect the API from overload.

### Architecture

```
Shared/
├── Infrastructure/
│   └── RateLimiting/
│       ├── Storage/           # Rate limit data storage
│       ├── Strategy/          # Limiting strategies
│       ├── Identifier/        # Client identification
│       ├── Configuration/     # Rate limit configuration
│       ├── Exception/         # Exceptions
│       └── EventListener/     # Symfony integration
└── Presentation/
    └── Http/
        └── Attribute/         # PHP attributes for declarative usage
```

### How it works

1. **Limit definition**: Limits are set declaratively via the PHP attribute `RateLimit`.
2. **Request identification**: Each request is identified by the client's IP address (by default).
3. **Request counting**: Redis stores request counters in keys with automatic expiration.
4. **Limit checking**: EventListener intercepts requests and checks if the limit is exceeded.
5. **Limit exceeded handling**: On exceeding the limit, HTTP 429 is returned with headers containing info.

### Usage

Add a rate limit to a controller or method:

```php
// Method-level limit
#[Route('/api/payments/create', methods: ['POST'])]
#[RateLimit(limit: 100, windowSizeSeconds: 60)] // 100 requests per minute
public function createPayment(Request $request): Response
{
    // ...
}

// Or class-level limit for all methods
#[RateLimit(limit: 1000, windowSizeSeconds: 3600)] // 1000 requests per hour
final class PaymentController
{
    // ...
}
```

### Key features

- **Flexibility**: Different limits for different endpoints
- **Scalability**: Redis provides high performance and cluster support
- **Extensibility**: Easy to add new strategies and identification methods
- **Standardization**: Standard HTTP headers are used for clients

### Response when rate limit is exceeded

```json
{
    "status": "error",
    "code": 101,
    "message": "Rate limit exceeded for resource 'api_route:create_payment'. Limit: 100. Wait 35 seconds before retrying.",
    "wait_seconds": 35
}
```

With HTTP headers:
- `X-RateLimit-Limit: 100`
- `X-RateLimit-Remaining: 0`
- `X-RateLimit-Reset: 1717133875` (Unix timestamp)
- `Retry-After: 35` (seconds)

### Configuration

Service configuration is in `src/Shared/Resources/config/rate_limiting.yaml`.

---

## Outbox Pattern

### Overview

The Outbox Pattern is an architectural pattern that ensures reliable delivery of domain events through asynchronous messaging, guaranteeing data consistency between different services in a distributed system. The main purpose of this pattern is to solve the "dual write" problem, ensuring that events are not lost in case of failures between their creation and publication.

### Why Outbox Pattern

1. **Atomicity** — Domain events and business data are stored in a single transaction
2. **Reliability** — Events are not lost even if the messaging system fails
3. **Consistency** — The system maintains consistency between domain state and published events
4. **Isolation** — Event processing happens asynchronously and doesn't block the main execution flow
5. **Idempotence** — The system can process the same event multiple times without side effects

### Architecture Implementation

```
Shared/
├── Infrastructure/
│   └── Outbox/
│       ├── Command/                              
│       │   ├── OutboxMessageEnvelope.php         # Message envelope
│       │   └── OutboxMessageEnvelopeHandler.php  # Envelope handler
│       ├── Publisher/                            
│       │   ├── OutboxPublisher.php               # Publisher implementation
│       │   └── OutboxPublisherInterface.php      # Publisher interface
│       ├── Repository/                           # Outbox storage operations
│       │   └── OutboxEventRepository.php         # Event repository
│       ├── Service/                              
│       │   └── OutboxEventProcessor.php          # Event processing service
│       └── ValueObject/                          
│           └── OutboxEvent.php                   # Outbox event class
└── Presentation/
    └── Console/
        └── Command/                 
            └── ProcessOutboxEventsCommand.php    # Event processing command
```

### How It Works

1. **Event Storage** — When the domain creates an event, it's serialized and stored in the `outbox_events` table along with business data transaction
2. **Event Processing** — A background process `OutboxEventProcessor` periodically polls the table for unprocessed events
3. **Envelope Creation** — Events are wrapped in an `OutboxMessageEnvelope` with metadata and payload
4. **Message Dispatch** — Envelopes are sent through a message bus (in our case, Symfony Messenger)
5. **Message Handling** — Events are processed by appropriate handlers in the system
6. **Event Deserialization** — `OutboxMessageEnvelopeHandler` deserializes the event payload from JSON using the static `fromArray()` method from the `DomainEventInterface`
7. **Event Bus Dispatch** — The deserialized domain event is dispatched to the event bus for further processing

### Implementation Benefits

1. **No Reflection** — We use the static `fromArray()` method for event deserialization without reflection
2. **Type Safety** — All events are strongly typed and implement a common interface
3. **Scalability** — Events can be processed in batches and in parallel
4. **Retry Mechanism** — Retry logic for events that failed to process
5. **Monitoring** — Tracking of processing success and errors

### Usage

#### Publishing Events via Repository

```php
// Domain changes automatically register events
$aggregate->doSomething();

// Saving the aggregate via repository publishes events
// The second parameter (true) indicates that events should be saved to the outbox
$this->repository->save($aggregate, true);
```

#### Manual Event Publication

```php
// Create domain event
$event = PaymentCompletedEvent::create($paymentId, $amount);

// Publish event through outbox
$this->outboxPublisher->publish($event);
```

#### Processing Outgoing Events

Starting the processing via console command:

```bash
bin/console app:process-outbox
```

Or via cron job:

```
* * * * * /path/to/project/bin/console app:process-outbox
```
### Event Processing Flows

#### Sending Event to Outbox

1. Aggregate creates domain event
2. Repository saves the aggregate and its events
3. `OutboxPublisher` serializes each event to JSON
4. Events are stored in `outbox_events` table with `is_processed = false`

#### Processing Event from Outbox

1. `OutboxEventProcessor` finds unprocessed events
2. Creates `OutboxMessageEnvelope` with metadata and payload
3. Sends envelope through message bus
4. `OutboxMessageEnvelopeHandler` deserializes event from JSON
5. Event is sent to event bus for further processing
6. Outbox event is marked as processed

### Error Handling

1. **Retry Mechanism** — If an error occurs during processing, the `retry_count` is increased
2. **Delayed Processing** — Events with errors can be processed later
3. **Error Logging** — Error description is saved in the `error` field

### Optimizations

1. **Row Locking** — Used to prevent parallel processing of the same event
2. **Batch Processing** — Events are processed in batches for better performance
3. **Table Cleanup** — Processed events can be periodically archived or deleted

---

## EventStore Pattern

### Overview

Event Store is a pattern used in event-sourced systems to persist and retrieve domain events. Unlike traditional data storage that keeps only the current state, Event Store maintains a complete history of all events that have affected an aggregate. This approach enables robust event sourcing, providing a reliable audit log, enhanced debugging capabilities, and the ability to reconstruct the state of any aggregate at any point in time.

### Architecture Implementation

```
Shared/
└── Infrastructure/
   └── EventStore
       ├── EventStoreInterface.php               # Event Storage Interface               
       └── Repository/                            
           └── OutboxEventRepository.php         # Event Storage Implementation
```

### Purpose

The Event Store pattern serves several crucial purposes:

1. **Immutable Record of Changes**: Captures all domain events as an immutable log of every change in the system
2. **Event Sourcing Support**: Enables rebuilding the state of any aggregate by replaying events
3. **Historical Analysis**: Allows for temporal queries and analyzing how the system evolved
4. **Audit Trail**: Provides a complete audit log for compliance and debugging
5. **System Resilience**: Facilitates recovery scenarios by replaying events

### Implementation Details

In our system, the Event Store is implemented using a Doctrine-based approach:

- **EventStoreInterface**: Defines the contract for storing and retrieving domain events
- **DoctrineEventStore**: Implements the interface using Doctrine DBAL
- **Database Structure**: Events are stored in a dedicated `event_store` table with the following schema:
  - `event_id`: Unique identifier for each event
  - `occurred_on`: Timestamp when the event occurred
  - `event_type`: Fully qualified class name of the event
  - `aggregate_id`: Identifier of the aggregate the event belongs to
  - `event_data`: Serialized event data in JSON format

### Event Store vs. Outbox Pattern

While both patterns deal with domain events, they serve different purposes:

- **Event Store**: Focuses on maintaining the complete history of domain events for event sourcing, allowing state reconstruction and historical analysis
- **Outbox Pattern**: Ensures reliable event publishing in distributed systems by temporarily storing events before they are dispatched to external systems

In some scenarios, both patterns can be used together - the Event Store maintains the complete event history, while the Outbox ensures reliable event delivery to external systems.

### Benefits

1. **Complete Audit Trail**: Every change to the domain is recorded and can be queried
2. **Time Travel Debugging**: The ability to reconstruct the state at any point in time
3. **Event Sourcing Support**: Enables advanced architectural patterns
4. **Decoupled Event Consumption**: Events can be consumed by various components without affecting the source
5. **Historical Analysis**: Enables business intelligence and analytics on historical data

### Considerations

1. **Performance**: Reading the current state requires replaying events, which can be optimized with snapshots
2. **Storage Growth**: The event store continuously grows as new events are appended
3. **Schema Evolution**: Care must be taken when changing event schemas to maintain backward compatibility
4. **Query Complexity**: Historical queries may require specialized approaches

---

## CQRS

Separation of read and write operations using the Command Query Responsibility Segregation pattern.

#### Architecture

- **CommandBus**: Command bus for state-changing operations
- **QueryBus**: Query bus for read operations
- **ApplicationService**: Centralized interface for both buses

#### Usage

```php
// Execute a command (mutation, no return value)
$this->applicationService->command(new CreatePaymentCommand($amount, $description));

// Execute a query (get data)
$payment = $this->applicationService->query(new GetPaymentQuery($paymentId));
```

### Specifications

Composable specifications for expressing business rules as logical expressions. Read: https://www.martinfowler.com/apsupp/spec.pdf

#### Available operators

- **AndSpecification**: Logical AND (&&)
- **OrSpecification**: Logical OR (||)
- **NotSpecification**: Logical NOT (!)
- **AndNotSpecification**: Logical AND-NOT (&& !)
- **OrNotSpecification**: Logical OR-NOT (|| !)

#### Usage

```php
$spec = new AndSpecification(
    new CustomerIsActiveSpecification(),
    new OrNotSpecification(
        new OrderHasItemsSpecification(),
        new OrderIsOverDueSpecification()
    )
);

if ($spec->isSatisfiedBy($customer)) {
    // Action if specification is satisfied
}