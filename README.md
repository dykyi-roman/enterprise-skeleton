# Enterprise Skeleton Project

A modern enterprise-grade application skeleton with Docker support and HTTPS configuration.

## Docker Configuration

The project uses Docker for containerization with the following services:

- **Web Server** (Configurable: Apache or Nginx)
  - HTTP Port: 1000
  - HTTPS Port: 1001
  - Apache Configuration:
    - Configuration: `infrastructure/containers/apache/apache.conf`
    - SSL Certificates: `infrastructure/containers/apache/ssl/`
  - Nginx Configuration:
    - Configuration: `etc/containers/nginx/site.conf`
    - SSL Certificates: `etc/containers/nginx/ssl/`

- **PHP-FPM** (Language)
  - Port: 9000
  - Configuration: `etc/containers/php/php.ini`

- **PostgreSQL** (Data Storage)
  - Port: 5432
  - Default Database: app
  - Default User: app
  - Configuration: `.env` file
  - Persistence: Docker volume

- **Redis** (Caching)
  - Port: 6379
  - Alpine-based image for lightweight footprint
  - Persistent data storage in `data/redis/`
  - Used for application caching to improve performance

- **RabbitMQ** (Message Queue)
  - AMQP Port: 5672
  - Management Interface Port: 15672
  - Default User: app
  - Management UI: http://localhost:15672
  - Used for asynchronous task processing and message queuing
  - Persistent message storage in `data/rabbitmq/`

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

## Getting Started

1. Clone the repository:
```bash
git clone [repository-url]
cd enterprise-skeleton
```

2. Start the Docker containers:
```bash
# Using Apache web server
make install server=apache

# Using Nginx web server
make install server=nginx
```

3. Access the application:
- HTTP: http://localhost:1000 (redirects to HTTPS)
- HTTPS: https://localhost:1001

Note: When accessing via HTTPS, you'll see a browser warning about the self-signed certificate in development. This is normal and can be safely bypassed for development purposes.

## Web Server Configuration

The project supports both Apache and Nginx web servers. You can switch between them using the `server` variable in the Makefile:

### Apache
- Modern SSL configuration with TLS 1.2/1.3
- PHP-FPM integration via mod_proxy_fcgi
- Automatic HTTP to HTTPS redirection
- Configuration files:
  - Main config: `infrastructure/containers/apache/apache2.conf`
  - Virtual hosts: `infrastructure/containers/apache/apache.conf`
  - SSL certificates: `infrastructure/containers/apache/ssl/`

### Nginx
- Event-driven architecture
- PHP-FPM integration
- Modern SSL configuration
- Configuration files:
  - Site config: `etc/containers/nginx/site.conf`
  - SSL certificates: `etc/containers/nginx/ssl/`

## Code Quality Tools

The project includes several code quality and analysis tools:

### PHP CS Fixer
- Automatically fixes PHP coding standards
- Configuration: `tools/php-cs-fixer.dist.php`
- Run: `make cs-check` or `make cs-fix`

### Deptrac
- Enforces architectural boundaries and dependencies
- `tools/deptrac-layers.yaml`: Checks dependencies between layers to maintain clean architecture
- `tools/deptrac-domain.yaml`: Validates dependencies between different domains to prevent unwanted coupling
- Run: `make deptrac`

### PHPStan
- Static analysis tool for finding code errors
- Configuration: `tools/phpstan.neon`
- Maximum level of strictness (level 8)
- Run: `make phpstan`

### Psalm
- Advanced static analysis and type checking
- Configuration: `tools/psalm.xml`
- Highest error level (level 1)
- Detects unused code and variables
- Run: `make psalm`

### PHPUnit
- Testing framework with automatic test suite discovery
- Configuration: `tools/phpunit.xml.dist`
- Automatically detects tests in `src/*/Tests` directories
- Run: `make test`

## Service Selection

The project supports multiple service options that can be configured in the Makefile:

### Available Service Options

```makefile
# Cache Options
cache = redis        # Choose between: redis, memcached

# Database Options
database = postgres  # Choose between: postgres, mysql, mongodb

# Message Broker Options
message = kafka      # Choose between: rabbitmq, kafka
```

### Using Different Services

You can specify which services to use by setting these variables in the Makefile or when running make commands:

```bash
# Example: Run with Redis, PostgreSQL, and Kafka
make up cache=redis database=postgres message=kafka

# Example: Run with Memcached, MongoDB, and RabbitMQ
make up cache=memcached database=mongodb message=rabbitmq
```

Each service combination is isolated and can be started independently, allowing you to use the exact infrastructure components needed for your specific use case.

### Default Configuration

The default configuration uses:
- Redis for caching
- PostgreSQL for database
- Kafka for message broker

## Database Management

The project uses PostgreSQL as its primary database with Doctrine ORM for database operations.

### Database Commands

The following Make commands are available for database management:

```bash
# Create a new migration after entity changes
make migration-create

# Run all pending migrations
make migration-run
```

### Database Configuration

The database connection is configured in the `.env` file:
```
DATABASE_URL="postgresql://app:password@postgres:5432/app?serverVersion=15&charset=utf8"
```

## Error Monitoring (Sentry)

The project has Sentry integration for error tracking. The following log routing is configured:

- All logs are written to `stdout` for general monitoring
- Logs of `error` and `critical` levels additionally:
  - Are sent to stderr
  - Automatically forwarded to Sentry

This allows centralized tracking and quick response to critical errors through the Sentry dashboard.

## Project Structure

### Modular Architecture
The project follows a domain-driven modular architecture:
- Each domain is a separate module in `src/`
- Modules are independent and loosely coupled
- Each module contains its own:
  - Business logic
  - Configuration
  - Dependencies
  - Tests

### Independent Configuration
- Configuration is modular and domain-specific
- Each module can have its own configuration
- Shared configuration is minimal and clearly separated
- Environment-specific settings use `.env` files

## Development Workflow

1. Start the Docker environment
2. Write your code following the modular structure
3. Run code quality tools before committing:
```bash
make cs-fix        # Fix code style
make phpstan       # Run static analysis
make deptrac       # Check dependencies
make psalm         # Run advanced static analysis
make test          # Run tests
```

## Production Deployment

For production deployment:

1. Replace the self-signed SSL certificates with valid certificates from a trusted Certificate Authority
2. Update the SSL configuration in `etc/containers/nginx/site.conf` if needed
3. Consider using Let's Encrypt for free, trusted SSL certificates

## Security Considerations

- Self-signed certificates are for development only
- Production environments should use trusted SSL certificates
- SSL private keys should never be committed to version control
- Regular certificate rotation is recommended
- Keep Docker images and dependencies up to date

## Network Configuration

The project uses a Docker network named `rta-network` for service communication. The network is configured as external and uses the bridge driver.

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request
