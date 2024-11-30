# Enterprise Skeleton Project

![img.png](img.png)

A comprehensive project skeleton for enterprise applications with integrated support for essential development services and best practices.

## Features

- 🚀 Modern PHP 8.3
- 🛠 Multiple web servers (Nginx, Apache)
- 📊 SQL & NoSQL databases
- 💾 Caching solutions
- 📨 Message brokers
- 🔍 Search engines
- 📊 Monitoring tools
- 📧 Mail testing
- 📝 Logging systems
- 📚 API documentation
- ⏰ Task scheduling

## Frameworks

<div style="display: flex; justify-content: space-around; align-items: center;">
    <a href="https://symfony.com" target="_blank">
        <img src="https://symfony.com/logos/symfony_dynamic_01.svg" alt="Symfony Logo" width="400">
    </a>
    <a href="https://laravel.com" target="_blank">
        <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
    </a>
</div>

---

## Quick Start

1. Copy configuration:
```bash
make copy-config
```

2. Configure environment:
   - Edit `infrastructure/config/cs-config` to customize services

3. Install and start:
```bash
make install
```

---

## Infrastructure Components

PHP container will be build automatically depend on which services you choose.

| Category            | Service                                     | Version                  | Access URL                                      |
|---------------------|---------------------------------------------|--------------------------|-------------------------------------------------|
| **Web Servers**     | Nginx<br>Apache                             | stable-alpine<br>2.4     | http://localhost:1000<br>https://localhost:1001 |
| **Databases**       | PostgreSQL<br>MySQL<br>MongoDB<br>Cassandra | 15<br>8.0<br>6.0<br>4.1  | -                                               |
| **Caching**         | Redis<br>Memcached                          | 7.2-alpine<br>1.6-alpine | -                                               |
| **Message Brokers** | RabbitMQ<br>Kafka                           | latest<br>latest         | http://localhost:15672<br>http://localhost:8080 |
| **Search**          | Elasticsearch<br>Solr                       | latest<br>latest         | -                                               |
| **Monitoring**      | Zabbix<br>Grafana                           | latest<br>latest         | http://localhost:8081<br>http://localhost:3000  |
| **Mail Testing**    | Mailhog<br>Papercut                         | latest<br>latest         | http://localhost:8025<br>http://localhost:37408 |
| **Logging**         | Kibana<br>Graylog                           | latest<br>latest         | http://localhost:5601<br>http://localhost:9400  |
| **Documentation**   | Swagger                                     | latest                   | http://localhost:8080/api/docs                  |

---

## Project Architecture

### Domain-Driven Structure
- Modular architecture in `src/` directory
- Independent domain modules
- Each module contains:
  - Domain logic
  - Infrastructure layer
  - Module-specific config
  - Test suite

### Adding New Domains
1. Create domain in `/src/YourDomain`
2. Register in `code/config/packages/domains.yaml`

---

## Development Tools

| Tool         | Purpose                 | Command             |
|--------------|-------------------------|---------------------|
| PHP CS Fixer | Code standards          | `make phpcs`        |
| Deptrac      | Architecture validation | `make deptrac`      |
| PHPStan      | Static analysis         | `make phpstan`      |
| Psalm        | Type checking           | `make psalm`        |
| PHPUnit      | Testing                 | `make test-php`     |
| Newman       | API testing             | `make test-postman` |

---

## Additional Features

### Health check

The project includes a set of health check commands to monitor various services.

All commands return:
- Success (0): When the service is available and functioning correctly
- Failure (1): When there are connection issues or service malfunctions

Usage example:
```bash
# Test MySQL connection
php bin/console app:healthcheck:mysql
```

### Request Tracking
- Automatic Request-ID generation
- UUID v4 format
- Header-based tracking

### SSL/HTTPS
- Development certificates included
- HTTP to HTTPS redirect
- TLS 1.2/1.3 support
- Custom certificate support (place in `etc/containers/nginx/ssl/`)

### Cron Jobs
- Docker-based scheduling
- Configure in `infrastructure/crontab`
- Monitor with `docker-compose exec cron crontab -l`

### API Testing
- Postman collection in `infrastructure/postman`
- Environment variables support
- Automated testing via Newman

---

## Configurations

### Graylog Setup

1. Install GELF PHP package:
```bash
composer req graylog2/gelf-php
```

2. Configure UDP Input:
  - Access http://localhost:9400
  - Navigate to System → Inputs
  - Add "GELF UDP" input on port 12201

### Elasticsearch, Kibana & Logstash (ELK) Configuration

1. Create index
```bash
  curl -X PUT http://localhost:9200/logs -H Content-Type: application/json -d {"settings":{"number_of_shards":1,"number_of_replicas":0},"mappings":{"properties":{"@timestamp":{"type":"date"},"message":{"type":"text"},"level":{"type":"keyword"},"channel":{"type":"keyword"},"context":{"type":"object"}}}}
```
2. Reload logstash

```bash
  docker compose -f infrastructure/docker-compose-tools.yml --profile elk restart logstash
```

---

## Contributing

We welcome contributions to the Enterprise Skeleton project! If you'd like to join the development effort, you can contribute by creating Pull Requests (PRs).

### Current Development Priorities

We are currently looking for contributions in the following areas:

- **Sentry Integration**: We need help implementing Sentry for error tracking and monitoring. If you have experience with Sentry integration in Symfony applications, we'd love your contribution!

- **RoadRunner Integration**: We are looking to integrate RoadRunner as a high-performance PHP application server. If you have experience with RoadRunner implementation, particularly in Symfony applications, your contribution would be valuable!

### How to Submit a Pull Request

1. Fork the repository
2. Create a new branch for your feature or fix
3. Make your changes following our coding standards
4. Write or update tests if necessary
5. Submit a Pull Request with a clear description of the changes
6. Ensure all checks pass (PHPStan, Psalm, CS-Fixer, etc.)

## License

This project is licensed under the MIT License.

## Author
[Dykyi Roman](https://dykyi-roman.github.io/)