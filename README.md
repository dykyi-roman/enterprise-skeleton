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

- **Search Engines**
  - Apache Solr
    - Port: 8983
    - Admin UI: http://localhost:8983/solr
    - Default Core: 'default'
    - Persistent data storage in `data/solr/`
    - Test command: `php bin/console app:test:solr`
  - Elasticsearch
    - Port: 9200
    - Internal Port: 9300
    - Test command: `php bin/console app:test:elasticsearch`
    - Used for full-text search and analytics

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

## API Documentation

The project includes Swagger UI for API documentation:

- **Swagger UI** (API Documentation)
  - Port: 8080 (configurable via SWAGGER_UI_PORT)
  - Access URL: http://localhost:8080/api/docs
  - OpenAPI Specification: Generated from PHP attributes in the code
  - Configuration: Available in `docker-compose.yml` under the `swagger-ui` service
  - Profile: Can be enabled/disabled using the "swagger" profile

### Using Swagger UI

1. Enable Swagger UI in your configuration:
```bash
# Enable in cs-config.dist or when running make commands
docs=swagger
```

2. Access the documentation:
- Visit http://localhost:8080/api/docs
- All API endpoints are automatically documented using PHP attributes
- Interactive testing of endpoints directly from the UI

## Request-ID Tracking

The application implements request tracking using Request-IDs with the following features:

- **Automatic Request-ID Generation**: 
  - Inspects incoming requests for the `Request-Id` header
  - If no request ID is found, automatically generates a version 4 UUID
  - Ensures every request has a unique identifier for tracking

- **Logging Integration**:
  - Includes Request-ID in Monolog records via a custom processor
  - Can be disabled by setting `enable_monolog: false` in configuration
  - Helps correlate logs across different services and components

- **Debugging and Tracing**:
  - Makes it easier to trace requests through the system
  - Useful for debugging and monitoring in distributed systems
  - Helps with request correlation in log aggregation systems

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

### Docker Configuration Optimization

For better build performance, the PHP container's Dockerfile has some service extensions commented out by default. When changing services in the Makefile, you'll need to:

1. Edit `infrastructure/containers/php/Dockerfile`
2. Uncomment the corresponding extension blocks for your chosen services
3. Rebuild the PHP container:
```bash
make build php
```

This approach ensures faster container builds by only including the extensions you actually need.

## Local Mail Testing

The project supports local mail testing through either MailHog or Papercut:

### MailHog
- SMTP Port: 1025
- Web Interface Port: 8025
- Web UI: http://localhost:8025
- Captures all outgoing emails for testing and development
- Provides a web interface to view email content, headers, and attachments

### Papercut
- SMTP Port: 25
- Web Interface Port: 37408
- Web UI: http://localhost:37408
- Simple SMTP server for testing email functionality
- Visual interface for inspecting sent emails

To use either service:
1. Configure your application's mailer settings to use the appropriate SMTP port
2. Send emails through your application as normal
3. View the captured emails in the respective web interface

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

## Initial Setup

1. Copy the configuration template:
```bash
make copy-config
```

2. Configure your environment:
Edit `infrastructure/config/cs-config` to enable/disable services. Available services:
```
server=nginx           # Web Server: nginx, apache
database=postgres      # Database Service: postgres, mysql, mongodb
cache=redis            # Cache Service: redis, memcached
search=elasticsearch   # Search: elasticsearch, solr
message=rabbitmq       # Message Broker: rabbitmq, kafka
docs=swagger           # API Documentation: swagger
mailer=mailhog         # Mail Sandbox: mailhog, papercut
```

3. Start the environment:
```bash
make install
```

## Available Services

### Web Servers
- Nginx
- Apache

### Databases
- PostgreSQL
- MySQL
- MongoDB

### Cache
- Redis
- Memcached

### Message Brokers
- RabbitMQ
- Kafka

## Debugging

To view current configuration:
```bash
make debug-config
```

## Notes

- Services are enabled/disabled through profiles in `infrastructure/config/cs-config`
- Each service can be configured through environment variables in `.env`
- For M1/M2 Macs, some services are configured to use platform-specific images

## License

This project is licensed under the MIT License - see the LICENSE file for details.
