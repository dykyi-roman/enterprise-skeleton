# Context Map

| Context | Description |
|---------|-------------|

# Architecture

# CQRS

Separation of read and write operations using the Command Query Responsibility Segregation pattern.

### Architecture

- **CommandBus**: Command bus for state-changing operations
- **QueryBus**: Query bus for read operations
- **ApplicationService**: Centralized interface for both buses

### Usage

```php
// Execute a command (mutation, no return value)
$this->applicationService->command(new CreatePaymentCommand($amount, $description));

// Execute a query (get data)
$payment = $this->applicationService->query(new GetPaymentQuery($paymentId));
```

# Hexagonal Architecture

## Overview

Hexagonal Architecture (also known as Ports & Adapters) is an architectural style that allows an application to be
managed equally well by users, programs, automated tests, or batch scripts, and to be developed and tested in isolation
from endpoints and databases.

## Key principles

1. **Business logic in the center** — domain logic is completely independent from the outside world
2. **Ports** — interfaces that define how the outside world can interact with the application
3. **Adapters** — port implementations for specific technologies or systems
4. **Dependencies only inward** — external layers depend on internal ones, but not vice versa

## Ports and adapters in our application

### Ports (interfaces)

Ports are interfaces that define interaction contracts between different layers:

1. **HTTP client**

- `Psr\Http\Client\ClientInterface` — standard interface for HTTP clients

2. **Caching**

- `Psr\SimpleCache\CacheInterface` — standard interface for simple caching

3. **Message bus**

- `/Shared/DomainModel/Service/MessageBusInterface.php` — interface for asynchronous message processing

4. **Data Access**

- Repository Interfaces (RepositoryInterface) — abstractions for working with data storage

### Adapters (implementations)

Adapters are specific implementations of ports that use specific technologies:

1. **HTTP Clients**

- Guzzle and other API clients — implementations for HTTP requests

2. **Caching**

- Symfony Cache Adapter — adapter for working with various caching systems

3. **Message Bus**

- `/Shared/Infrastructure/MessageBus/SymfonyMessageBus.php` — implementation of a message bus based on Symfony Messenger

4. **Data Access**

- `/code/src/Shared/Infrastructure/Persistence/Doctrine/Repository/AbstractDoctrineRepository.php` — basic repository
  based on Doctrine ORM

## Advantages of hexagonal architecture

1. **Testability** — domain logic can be tested without depending on infrastructure
2. **Flexibility** — easy to replace components without changing business logic
3. **Maintainability** — clear separation of concerns simplifies maintenance and development
4. **Isolation** — domain logic is protected from changes in external systems
5. **Parallel development** — teams can work on different layers independently

---

# Clean Architecture

## Overview

Clean Architecture is a software design philosophy that separates the elements of a design into concentric layers. The main goal is to produce systems that are:
- Independent of frameworks
- Testable
- Independent of the UI
- Independent of the database
- Independent of any external agency

## Dependency Rule

The fundamental rule of Clean Architecture is that dependencies always point inward. This means:
- Domain Layer has no outward dependencies
- Application Layer depends only on Domain Layer
- Presentation Layer depends on Application and Domain Layers
- Infrastructure Layer depends on all other layers

## Benefits of Clean Architecture

1. **Separation of Concerns** — Each layer has a clear responsibility
2. **Testability** — Core business logic can be tested without external dependencies
3. **Maintainability** — Changes in external frameworks or technologies have minimal impact on business logic
4. **Flexibility** — Components can be replaced with minimal disruption
5. **Scalability** — The architecture supports growth and changing requirements

---

# Layer Architecture

## Overview

Layer Architecture organizes the codebase into horizontal layers, each with a specific responsibility. This approach creates a clear separation between different parts of the application, making it easier to understand, maintain, and extend.

## Our Layer Structure

Our application is organized into the following layers:

1. **Domain Layer**
   - Core business logic and rules
   - Contains entities, value objects, domain events, domain services...
   - Has no dependencies on other layers

2. **Application Layer**
   - Use cases and business operations
   - Implements CQRS pattern with commands and queries
   - Provides an API for the presentation layer
   - Depends only on the domain layer

3. **Presentation Layer**
   - User interfaces (Web) and API endpoints
   - Converts between domain/application objects and external representations
   - Handles HTTP requests and responses
   - Depends on application layer

4. **Infrastructure Layer**
   - Technical implementation details
   - Database access, external services, messaging systems
   - Implements domain interfaces (e.g., repositories)
   - It May depend on all other layers

## Communication Between Layers

- **Top-down dependency**: Each layer depends only on layers below it
- **Interfaces at boundaries**: Domain defines interfaces that infrastructure implements
- **DTOs for data transfer**: Data Transfer Objects for crossing layer boundaries
- **CQRS for operations**: Commands and queries separate write and read operations

## Benefits of Layer Architecture

1. **Modularity** — Changes in one layer have minimal impact on others
2. **Reusability** — Lower layers can be reused by different higher layers
3. **Portability** — Upper layers can be ported to different infrastructure implementations
4. **Testability** — Layers can be tested in isolation with mocked dependencies
5. **Separation of technical and domain concerns** — Technical details isolated from business logic

---