# Code - Enterprise Application

This directory contains the main application source code built with **Laravel 12** and **PHP 8.5**, following **Domain-Driven Design (DDD)** principles and **Clean Architecture**.

## Table of Contents

- [Technology Stack](#technology-stack)
- [Directory Structure](#directory-structure)
- [Architecture](#architecture)
- [Shared Module](#shared-module)
- [Architectural Patterns](#architectural-patterns)
- [Configuration](#configuration)
- [Creating a New Domain](#creating-a-new-domain)
- [Code Quality Tools](#code-quality-tools)
- [Commands](#commands)
- [Healthcheck Module](#healthcheck-module)
- [License](#license)

## Technology Stack

| Component   | Version                      |
|-------------|------------------------------|
| PHP         | 8.5 (strict typing required) |
| Laravel     | 12.0                         |
| PHPUnit     | 11.0                         |
| PHPStan     | 2.0 (via Larastan)           |
| Psalm       | 5.18                         |

## Directory Structure

```
code/
├── artisan                 # Laravel CLI entry point
├── bootstrap/              # Application bootstrap
│   └── app.php             # Application configuration
├── config/                 # Framework configuration
│   ├── app.php             # Application providers & aliases
│   ├── cache.php           # Cache configuration
│   ├── database.php        # Database connections
│   ├── logging.php         # Logging channels
│   ├── mail.php            # Mail configuration
│   ├── queue.php           # Queue connections
│   └── session.php         # Session configuration
├── docs/                   # Documentation
│   └── api/                # API documentation (OpenAPI)
├── public/                 # Web root (index.php)
├── resources/              # Views and assets
│   └── views/              # Blade templates
├── src/                    # Application source code (DDD)
├── storage/                # Runtime data (cache, logs, sessions)
├── tests/                  # Test suite
│   ├── Feature/            # Feature tests
│   └── Unit/               # Unit tests
└── vendor/                 # Composer dependencies
```

## Architecture

The application follows a **layered DDD architecture** with the following structure:

```
src/
├── Shared/                 # Cross-cutting concerns (core foundation)
├── CoreDomain/             # Example business domain (template)
└── Healthcheck/            # Health monitoring domain
```

### Layer Responsibilities

| Layer              | Purpose                                        | Examples                               |
|--------------------|------------------------------------------------|----------------------------------------|
| **Presentation**   | Handles HTTP/CLI requests, maps input to DTOs  | Actions, Commands, Requests, Responses |
| **Application**    | Business operations, orchestrates domain logic | UseCases, Application Services         |
| **Domain**         | Core business logic, no external dependencies  | Entities, Value Objects, Domain Events |
| **Infrastructure** | Technical implementations                      | Repositories, Clients, Persistence     |

### Domain Module Structure

Each domain follows a consistent structure:

```
src/YourDomain/
├── Application/            # Use cases and application services
│   └── UseCases/
├── DomainModel/            # Core business logic
│   ├── Model/              # Entities and Aggregates
│   └── Repository/         # Repository interfaces
├── Infrastructure/         # Technical implementations
│   └── Persistence/        # Repository implementations
├── Presentation/           # HTTP and CLI interfaces
│   ├── Api/                # REST API actions
│   │   └── Response/       # API responders
│   ├── Console/            # Artisan commands
│   └── Web/                # Web controllers
│       ├── Request/        # Form requests
│       └── Response/       # HTML responders
├── Resources/              # Domain resources
│   ├── Attribute/          # Route attributes
│   ├── Config/             # Domain configuration
│   └── Views/              # Blade templates
└── Tests/                  # Domain tests
    └── Unit/
```

## Shared Module

The `Shared/` module provides foundational components used across all domains:

### Presentation (`Shared/Presentation/`)

| Component                     | Purpose                              |
|-------------------------------|--------------------------------------|
| `Api/AbstractApiAction`       | Base class for API actions           |
| `Responder/ResponderInterface`| Response contract                    |
| `Responder/JsonResponder`     | JSON response middleware             |
| `Responder/HtmlResponder`     | HTML response middleware             |
| `Responder/AbstractResponder` | Base responder implementation        |

### Resources (`Shared/Resources/`)

| Component                  | Purpose                          |
|----------------------------|----------------------------------|
| `ResponderServiceProvider` | Registers responder middlewares  |

## Architectural Patterns

### 1. ADR (Action-Domain-Responder)

Presentation layer follows the ADR pattern for clean request handling:

- **Action** - Receives HTTP request, calls use case (single responsibility)
- **Domain** - Business logic (UseCase)
- **Responder** - Formats and returns response (JSON/HTML/Template)

```php
#[ApiRoute('/api/test', ['GET'], 'api.test')]
final class TestAction extends AbstractApiAction
{
    public function __invoke(TestJsonResponder $responder): ResponderInterface
    {
        return $responder->success('Success!')->respond();
    }
}
```

### 2. Attribute-Based Routing

Routes are defined using PHP 8 attributes on action classes:

```php
// API routes
#[ApiRoute('/api/users', ['GET', 'POST'], 'api.users')]

// Web routes
#[WebRoute('/dashboard', ['GET'], 'web.dashboard')]
```

Route attributes are automatically discovered and registered by `DomainServiceProvider`.

### 3. Responder Pattern

Responses are handled through dedicated responder classes:

```php
final class TestJsonResponder
{
    public function success(string $message): self
    {
        $this->payload = ['success' => true, 'message' => $message];
        return $this;
    }

    public function error(string $message): self
    {
        $this->payload = ['success' => false, 'error' => $message];
        return $this;
    }
}
```

### 4. Service Provider Pattern

Each domain has its own `DomainServiceProvider` that:

- Registers routes from Action classes with `#[Route]` attributes
- Registers console commands with `#[AsCommand]` attributes
- Loads domain-specific views and configuration

## Configuration

### Environment Files

| File              | Purpose                       |
|-------------------|-------------------------------|
| `.env`            | Default environment variables |
| `.env.local`      | Local overrides (gitignored)  |
| `.env.testing`    | Test environment              |

### Key Configuration Files (`config/`)

| File              | Purpose                        |
|-------------------|--------------------------------|
| `app.php`         | Providers, aliases, timezone   |
| `database.php`    | Database connections           |
| `cache.php`       | Cache stores (file, redis)     |
| `queue.php`       | Queue connections              |
| `logging.php`     | Log channels                   |
| `mail.php`        | Mail configuration             |
| `session.php`     | Session handling               |

### Registering Domain Providers

Add your domain provider to `config/app.php`:

```php
'providers' => [
    // ... Laravel providers
    App\YourDomain\Resources\DomainServiceProvider::class,
],
```

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
├── Resources/
│   ├── Attribute/
│   └── Config/
└── Tests/
    └── Unit/
```

2. Create `DomainServiceProvider`:

```php
<?php

declare(strict_types=1);

namespace App\YourDomain\Resources;

use Illuminate\Support\ServiceProvider;

final class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerRoutes();
        $this->registerCommands();
    }

    // ... route and command registration logic
}
```

3. Register provider in `config/app.php`:

```php
'providers' => [
    App\YourDomain\Resources\DomainServiceProvider::class,
],
```

4. Add namespace to `composer.json`:

```json
{
    "autoload": {
        "psr-4": {
            "App\\YourDomain\\": "src/YourDomain/"
        }
    }
}
```

5. Regenerate autoloader:

```bash
composer dump-autoload
```

## Code Quality Tools

| Tool         | Configuration               | Purpose                 |
|--------------|-----------------------------|-------------------------|
| PHPStan      | `phpstan.dist.neon`         | Static analysis         |
| Larastan     | via PHPStan                 | Laravel-specific rules  |
| Deptrac      | `deptrac.yaml`              | Architecture validation |
| PHP CS Fixer | `tools/.php-cs-fixer.php`   | Code standards (PSR-12) |
| PHPUnit      | `phpunit.xml` / `tools/`    | Unit testing            |
| Psalm        | `tools/psalm.xml`           | Type checking           |
| Pint         | Built-in                    | Laravel code style      |

## API Documentation

OpenAPI specification located at `docs/api/openapi.yaml`.

Generate documentation from code annotations:

```bash
./vendor/bin/openapi src -o docs/api/openapi.yaml
```

## Commands

```bash
# Run artisan commands
php artisan list

# Run tests
php artisan test
./vendor/bin/phpunit

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# All caches at once
php artisan optimize:clear

# Run queue worker
php artisan queue:work

# Development server
php artisan serve

# Interactive REPL
php artisan tinker
```

## Healthcheck Module

Reference implementation with 16 health check commands for all supported services:

| Command                        | Service       |
|--------------------------------|---------------|
| `healthcheck:amqp`             | RabbitMQ      |
| `healthcheck:cassandra`        | Cassandra     |
| `healthcheck:elasticsearch`    | Elasticsearch |
| `healthcheck:grafana`          | Grafana       |
| `healthcheck:graylog`          | Graylog       |
| `healthcheck:kafka`            | Kafka         |
| `healthcheck:log`              | Logging       |
| `healthcheck:logstash`         | Logstash      |
| `healthcheck:mail`             | Mail          |
| `healthcheck:memcache`         | Memcached     |
| `healthcheck:mongodb`          | MongoDB       |
| `healthcheck:mysql`            | MySQL         |
| `healthcheck:postgres`         | PostgreSQL    |
| `healthcheck:redis`            | Redis         |
| `healthcheck:solr`             | Solr          |
| `healthcheck:zabbix`           | Zabbix        |

Usage:

```bash
php artisan healthcheck:postgres
php artisan healthcheck:redis
php artisan healthcheck:elasticsearch
```

All commands return:
- `0` (Success): Service is available and functioning
- `1` (Failure): Connection issues or service malfunction

## License

See the main project LICENSE file.
