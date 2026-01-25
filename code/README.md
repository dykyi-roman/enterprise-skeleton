# Code - Enterprise Application

This directory contains the main application source code built with **Symfony 8.0** and **PHP 8.5**, following **Domain-Driven Design (DDD)** principles and **Clean Architecture**.

## Technology Stack

| Component    | Version                      |
|--------------|------------------------------|
| PHP          | 8.5 (strict typing required) |
| Symfony      | 8.0                          |
| Doctrine ORM | 3.3                          |
| PHPUnit      | 12.1                         |

## Directory Structure

```
code/
├── bin/                    # Executable scripts (console, phpunit)
├── bootstrap/              # Application bootstrap
├── config/                 # Framework configuration
│   ├── packages/           # Bundle configurations
│   └── routes/             # Routing configuration
├── docs/                   # API documentation (OpenAPI)
├── migrations/             # Database migrations
├── public/                 # Web root (index.php)
├── src/                    # Application source code
├── tests/                  # Test bootstrap
├── var/                    # Runtime data (cache, logs)
└── vendor/                 # Composer dependencies
```

## Architecture

The application follows a **layered DDD architecture** with the following structure:

```
src/
├── Framework/              # Symfony Kernel
├── Shared/                 # Cross-cutting concerns (core foundation)
├── CoreDomain/             # Example business domain
└── Healthcheck/            # Health monitoring domain
```

### Layer Responsibilities

| Layer              | Purpose                                        | Examples                               |
|--------------------|------------------------------------------------|----------------------------------------|
| **Presentation**   | Handles HTTP/CLI requests, maps input to DTOs  | Actions, Commands, Requests, Responses |
| **Application**    | Business operations, orchestrates domain logic | UseCases, Application Services         |
| **Domain**         | Core business logic, no external dependencies  | Entities, Value Objects, Domain Events |
| **Infrastructure** | Technical implementations                      | Repositories, Message Bus, Persistence |

## Shared Module

The `Shared/` module provides foundational components used across all domains:

### Domain Model (`Shared/DomainModel/`)

- **Entity/** - `AbstractAggregateRoot`, `AggregateRootInterface`
- **Enum/** - `GeneralErrorCode`
- **Event/** - `DomainEventInterface`, `AbstractDomainEvent`
- **Exception/** - `DomainException`
- **Repository/** - `PaginatedRepositoryInterface`
- **Service/** - Interfaces for `MessageBus`, `TransactionService`, `EventStore`, `RequestIdService`
- **Specification/** - Design pattern implementations (Composite, And, Or, Not)
- **ValueObject/** - `AbstractValueObject`, `RequestId`

### Infrastructure (`Shared/Infrastructure/`)

- **ErrorHandler/** - Global error handling
- **EventStore/** - Doctrine event store implementation
- **HttpClient/** - Logging middleware and factory
- **MessageBus/** - Symfony message bus implementation
- **Outbox/** - Event sourcing pattern (transactional outbox)
- **Persistence/** - Doctrine integration (repositories, pagination, transactions)
- **RateLimiting/** - API rate limiting with configurable strategies
- **RequestId/** - Request tracking for distributed logging

### Presentation (`Shared/Presentation/`)

- **Api/** - `AbstractApiAction` base class
- **Console/** - `AbstractConsoleCommand`, console output handling
- **Responder/** - Response patterns (JSON, HTML, Twig, Console)

## Architectural Patterns

### 1. CQRS (Command Query Responsibility Segregation)

Three separate message buses configured in `config/services.yaml`:

- `command.bus` - Handles commands (write operations)
- `query.bus` - Handles queries (read operations)
- `event.bus` - Handles domain events

### 2. Event Sourcing with Outbox Pattern

Reliable event publishing using transactional outbox:

```
Outbox/
├── Command/            # OutboxMessageEnvelope handling
├── Publisher/          # OutboxPublisher interface & implementation
├── Repository/         # OutboxEventRepository
├── Service/            # OutboxEventProcessor
└── ValueObject/        # OutboxEvent VO
```

### 3. ADR (Action-Domain-Responder)

Presentation layer follows ADR pattern:

- **Action** - Receives HTTP request, calls use case
- **Domain** - Business logic (UseCase)
- **Responder** - Formats and returns response

### 4. Specification Pattern

Composable business rules:

```php
$spec = new AndSpecification(
    new ActiveUserSpecification(),
    new PremiumUserSpecification()
);
$spec->isSatisfiedBy($user);
```

## Configuration

### Environment Files

| File        | Purpose                       |
|-------------|-------------------------------|
| `.env`      | Default environment variables |
| `.env.dev`  | Development overrides         |
| `.env.test` | Test environment              |

### Bundle Configuration (`config/packages/`)

| File                       | Purpose                    |
|----------------------------|----------------------------|
| `cache.yaml`               | Cache strategy             |
| `doctrine.yaml`            | Database ORM configuration |
| `doctrine_migrations.yaml` | Migration settings         |
| `domains.yaml`             | Domain module imports      |
| `framework.yaml`           | Framework settings         |
| `lock.yaml`                | Distributed locks          |
| `messenger.yaml`           | Message buses (CQRS)       |
| `monolog.yaml`             | Logging configuration      |

## Creating a New Domain

1. Create domain directory structure:

```
src/YourDomain/
├── Application/
│   └── UseCases/
├── DomainModel/
│   ├── Model/
│   └── Repository/
├── Infrastructure/
│   └── Persistence/
├── Presentation/
│   ├── Api/
│   ├── Console/
│   └── Web/
├── Tests/
│   └── Unit/
└── Resources/
    └── Config/
```

2. Register domain in `config/packages/domains.yaml`:

```yaml
imports:
    - { resource: '../../src/YourDomain/Resources/Config/' }
```

3. Add namespace to `composer.json`:

```json
{
    "autoload": {
        "psr-4": {
            "YourDomain\\": "src/YourDomain/"
        }
    }
}
```

## Code Quality Tools

| Tool         | Configuration             | Purpose                 |
|--------------|---------------------------|-------------------------|
| PHPStan      | `phpstan.dist.neon`       | Static analysis         |
| Deptrac      | `deptrac.yaml`            | Architecture validation |
| PHP CS Fixer | `tools/.php-cs-fixer.php` | Code standards (PSR-12) |
| PHPUnit      | `tools/phpunit.xml.dist`  | Unit testing            |
| Psalm        | `tools/psalm.xml`         | Type checking           |

## API Documentation

OpenAPI specification located at `docs/api/openapi.yaml`.

## Commands

```bash
# Run console commands
./bin/console list

# Run tests
./bin/phpunit

# Clear cache
./bin/console cache:clear

# Run migrations
./bin/console doctrine:migrations:migrate

# Process outbox events
./bin/console outbox:process
```

## Healthcheck Module

Reference implementation with 19 health check commands for all supported services:

- AMQP, Cassandra, Elasticsearch, Grafana, Graylog
- Kafka, Logstash, Mail, Memcache, MongoDB
- MySQL, PostgreSQL, Redis, Solr, Zabbix

## License

See the main project LICENSE file.
