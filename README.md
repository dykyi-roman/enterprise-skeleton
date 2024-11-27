# Enterprise Skeleton Project

A modern enterprise-grade application skeleton with Docker support and HTTPS configuration.

# Initial Setup

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
monitoring=zabbix      # Monitoring: zabbix, grafana
```

3. Start the environment:
```bash
make install
```

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

# Code Quality Tools

The project includes several code quality and analysis tools:

### PHP CS Fixer
- Automatically fixes PHP coding standards
- Run: `make cs-check` or `make cs-fix`

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

## Local Mail Testing

The project supports local mail testing through either MailHog or Papercut:

## Database Management

The project uses PostgreSQL as its primary database with Doctrine ORM for database operations.

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

## Security Considerations

- Self-signed certificates are for development only
- Production environments should use trusted SSL certificates
- SSL private keys should never be committed to version control
- Regular certificate rotation is recommended
- Keep Docker images and dependencies up to date

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

## Monitoring Stack

The project includes a comprehensive monitoring setup with Prometheus, Pushgateway, Grafana, and Zabbix:

### Components

- **Prometheus** (Metrics Collection)
  - UI: http://localhost:9090
  - Used for collecting and storing metrics

- **Pushgateway** (Metrics Ingestion)
  - UI: http://localhost:9091
  - Used for pushing metrics from batch jobs and CLI commands

- **Grafana** (Visualization)
  - UI: http://localhost:3000
  - Default credentials: admin/admin
  - Used for creating dashboards and visualizing metrics

- **Zabbix** (Monitoring and Alerting)
  - Web UI: http://localhost:8080
  - Default credentials: Admin/zabbix
  - Server Port: 10051
  - Features:
    - Dedicated PostgreSQL database
    - Built-in web interface with Nginx
    - PHP integration via custom healthcheck command
    - Real-time monitoring and alerting
    - Custom metrics support

3. Access monitoring interfaces:
   - Zabbix: http://localhost:8080
   - Prometheus: http://localhost:9090
   - Grafana: http://localhost:3000

### Integration

- PHP applications can send metrics directly to Zabbix using the built-in healthcheck command
- Prometheus metrics can be visualized in Grafana dashboards
- Zabbix provides its own visualization and alerting capabilities

## Notes

- Services are enabled/disabled through profiles in `infrastructure/config/cs-config`
- Each service can be configured through environment variables in `.env`
- For M1/M2 Macs, some services are configured to use platform-specific images

## License

This project is licensed under the MIT License - see the LICENSE file for details.
