# Enterprise Skeleton Project

A modern enterprise-grade application skeleton with Docker support and HTTPS configuration.

---

# Initial Setup

1. Copy the configuration template:
```bash
make copy-config
```

2. Configure your environment:
   Edit `infrastructure/config/cs-config` to enable/disable services.

3. Start:
```bash
make install
```

# Infrastructure

### Web Servers
- Nginx
- Apache

### Databases
- PostgreSQL
- MySQL

### NoSQL Database
- MongoDB
- Cassandra

### Cache
- Redis
- Memcached

### Message Brokers
- RabbitMQ
- Kafka

### Search
- elasticsearch
- solr

### Monitoring
- zabbix
- grafana

### Mail Sandbox
- mailhog
- papercut

### API Documentation
- swagger

### Scheduling jobs
- cron

---

# Project Structure

## Modular Architecture
The project follows a domain-driven modular architecture:
- Each domain is a separate module in `src/`
- Modules are independent and loosely coupled
- Each module contains its own:
  - Business logic
  - Configuration
  - Dependencies
  - Tests

## Independent Configuration
- Configuration is modular and domain-specific
- Each module can have its own configuration
- Shared configuration is minimal and clearly separated
- Environment-specific settings use `.env` files

## How adding new domain models

1. Add new Domain Model to the `/src` directory by example `code/src/YourDomain`
2. Register it in the domain configuration `code/config/packages/domains.yaml`

### Health check

The project includes a set of health check commands to monitor various services:

| Command                         | Description                                               |
|---------------------------------|-----------------------------------------------------------|
| `app:healthcheck:mysql`         | Tests MySQL database connection and basic operations      |
| `app:healthcheck:postgres`      | Tests PostgreSQL database connection and basic operations |
| `app:healthcheck:mongodb`       | Tests MongoDB connection availability                     |
| `app:healthcheck:cassandra`     | Tests Cassandra connection availability                   |
| `app:healthcheck:redis`         | Tests Redis cache server connection                       |
| `app:healthcheck:memcache`      | Tests Memcache server connection                          |
| `app:healthcheck:amqp`          | Tests RabbitMQ message broker connection                  |
| `app:healthcheck:kafka`         | Tests Apache Kafka message broker connection              |
| `app:healthcheck:elasticsearch` | Tests Elasticsearch search engine connection              |
| `app:healthcheck:solr`          | Tests Apache Solr search engine connection                |
| `app:healthcheck:mail`          | Tests mail server connection and configuration            |
| `app:healthcheck:zabbix`        | Tests Zabbix monitoring integration                       |
| `app:healthcheck:graphana`      | Tests Grafana monitoring integration                      |
| `app:healthcheck:log`           | Tests logging system configuration                        |

All commands return:
- Success (0): When the service is available and functioning correctly
- Failure (1): When there are connection issues or service malfunctions

Usage example:
```bash
# Test MySQL connection
php bin/console app:healthcheck:log
```
---

# Tools

The project includes several code quality and analysis tools:

### PHP CS Fixer
- Automatically fixes PHP coding standards
- Run: `make phpcs`

### Deptrac
- Enforces architectural boundaries and dependencies
- `tools/deptrac-layers.yaml`: Checks dependencies between layers to maintain clean architecture
- `tools/deptrac-domain.yaml`: Validates dependencies between different domains to prevent unwanted coupling
- Run: `make deptrac`

### PHPStan
- Static analysis tool for finding code errors
- Run: `make phpstan`

### Psalm
- Advanced static analysis and type checking
- Run: `make psalm`

### PHPUnit
- Testing framework with automatic test suite discovery
- Run: `make test-php`

### Newman
- Testing Postman collection using Newman
- Run: `make test-postman`
---

## Postman Collection

The project includes a Postman collection located at: `infrastructure/postman`

The collection includes examples for authorization, API endpoints, and automated tests.

### How to Use
1. Open [Postman](https://www.postman.com/).
2. Import the collection file from `infrastructure/postman`.
3. Set up environment variables like `base_url` and `auth_token` if needed.
4. Use the ready-to-go requests to interact with the API.

### Automated Testing with Newman
- Newman is integrated for automated Postman collection testing
- Run tests using: `make test-postman`

## Request-ID Tracking

The application implements request tracking using Request-IDs with the following features:

- **Automatic Request-ID Generation**:
  - Inspects incoming requests for the `Request-Id` header
  - If no request ID is found, automatically generates a version 4 UUID
  - Ensures every request has a unique identifier for tracking

## SSL/HTTPS Support

The project includes HTTPS support with the following features:

- Self-signed SSL certificates for development
- Automatic HTTP to HTTPS redirect
- Modern SSL protocols (TLSv1.2, TLSv1.3)
- Secure cipher configuration

### SSL Certificates

For development, self-signed certificates are used. For production, replace the certificates in `etc/containers/nginx/ssl/` with your own SSL certificates:
- `nginx-selfsigned.crt`: SSL certificate
- `nginx-selfsigned.key`: Private key

---

# Contributing

We welcome contributions to the Enterprise Skeleton project! If you'd like to join the development effort, you can contribute by creating Pull Requests (PRs).

### Current Development Priorities

We are currently looking for contributions in the following areas:

- **Sentry Integration**: We need help implementing Sentry for error tracking and monitoring. If you have experience with Sentry integration in Symfony applications, we'd love your contribution!

### How to Submit a Pull Request

1. Fork the repository
2. Create a new branch for your feature or fix
3. Make your changes following our coding standards
4. Write or update tests if necessary
5. Submit a Pull Request with a clear description of the changes
6. Ensure all checks pass (PHPStan, Psalm, CS-Fixer, etc.)

---

## License

This project is licensed under the MIT License.

## Author
[Dykyi Roman](https://dykyi-roman.github.io/)