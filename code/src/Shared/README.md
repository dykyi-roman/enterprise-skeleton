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

A mechanism for reliable delivery of domain events via asynchronous messages.

#### Architecture

- **OutboxEventProcessor**: Processes unsent events from storage
- **OutboxMessageEnvelope**: Envelope for messages with metadata
- **OutboxMessageEnvelopeHandler**: Converts messages back to domain events

#### Usage

Domain events are published via the abstract repository:

```php
// Domain changes automatically register events
$aggregate->doSomething();

// Saving the aggregate via repository publishes events
$this->repository->save($aggregate);
```

Event processing is started with the command:

```bash
bin/console app:process-outbox
```

### CQRS

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

Composable specifications for expressing business rules as logical expressions.

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