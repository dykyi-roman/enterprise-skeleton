# Enterprise Skeleton Project

A modern enterprise-grade application skeleton with Docker support and HTTPS configuration.

## Requirements

- Docker
- Docker Compose
- Git

## Docker Configuration

The project uses Docker for containerization with the following services:

- **Nginx** (Web Server)
  - HTTP Port: 1000
  - HTTPS Port: 1001
  - Configuration: `etc/containers/nginx/site.conf`
  - SSL Certificates: `etc/containers/nginx/ssl/`

- **PHP-FPM**
  - Port: 9000
  - Configuration: `etc/containers/php/php.ini`

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
cd etc
docker compose up -d --build
```

3. Access the application:
- HTTP: http://localhost:1000 (redirects to HTTPS)
- HTTPS: https://localhost:1001

Note: When accessing via HTTPS, you'll see a browser warning about the self-signed certificate in development. This is normal and can be safely bypassed for development purposes.

## Code Quality Tools

The project includes several code quality and analysis tools:

### PHP CS Fixer
- Automatically fixes PHP coding standards
- Configuration: `tools/php-cs-fixer.dist.php`
- Run: `make cs-check` or `make cs-fix`

### Deptrac
- Enforces architectural boundaries and dependencies
- Configuration: `tools/deptrac.yaml`
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
