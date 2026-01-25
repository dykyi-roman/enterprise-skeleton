# Infrastructure

This directory contains Docker-based infrastructure configuration for the enterprise skeleton project. It provides a complete development and production-ready environment with 20+ services.

## Directory Structure

```
infrastructure/
├── containers/             # Container-specific configurations
│   ├── apache/             # Apache web server
│   ├── grafana/            # Monitoring dashboards
│   ├── logstash/           # Log processing
│   ├── nginx/              # Nginx web server
│   ├── php/                # PHP-FPM container
│   ├── postgres/           # PostgreSQL database
│   └── prometheus/         # Metrics collection
├── config/                 # Infrastructure configuration
├── cron/                   # Scheduled tasks
├── data/                   # Data persistence volumes
├── scripts/                # Build & deployment scripts
├── docker-compose.yml      # Main services composition
├── docker-compose-tools.yml # Additional development tools
└── .env                    # Environment variables
```

## Supported Services

### Web Servers

| Service | Image          | Port    | Profile  |
|---------|----------------|---------|----------|
| Nginx   | `nginx:latest` | 80, 443 | `nginx`  |
| Apache  | `httpd:2.4`    | 80, 443 | `apache` |

### Databases

| Service    | Image                | Port  | Profile     |
|------------|----------------------|-------|-------------|
| PostgreSQL | `postgres:15-alpine` | 5432  | `postgres`  |
| MySQL      | `mysql:8.0`          | 3306  | `mysql`     |
| MongoDB    | `mongo:6.0`          | 27017 | `mongodb`   |
| Cassandra  | `cassandra:4.1`      | 9042  | `cassandra` |

### Cache & Session

| Service   | Image              | Port  | Profile     |
|-----------|--------------------|-------|-------------|
| Redis     | `redis:7.2-alpine` | 6379  | `redis`     |
| Memcached | `memcached:1.6`    | 11211 | `memcached` |

### Message Brokers

| Service  | Image                   | Port        | Profile    |
|----------|-------------------------|-------------|------------|
| RabbitMQ | `rabbitmq:3-management` | 5672, 15672 | `rabbitmq` |
| Kafka    | `bitnami/kafka:3.3.1`   | 9092        | `kafka`    |

### Search Engines

| Service       | Image                  | Port | Profile         |
|---------------|------------------------|------|-----------------|
| Elasticsearch | `elasticsearch:8.11.1` | 9200 | `elasticsearch` |
| Solr          | `solr:9.4`             | 8983 | `solr`          |

### Monitoring & Metrics

| Service       | Image                                      | Port  | Profile      |
|---------------|--------------------------------------------|-------|--------------|
| Prometheus    | `prom/prometheus:latest`                   | 9090  | `prometheus` |
| Grafana       | `grafana/grafana:latest`                   | 3000  | `grafana`    |
| Zabbix Server | `zabbix/zabbix-server-pgsql:6.4-alpine`    | 10051 | `zabbix`     |
| Zabbix Web    | `zabbix/zabbix-web-nginx-pgsql:6.4-alpine` | 8080  | `zabbix`     |

### Logging

| Service  | Image                 | Port | Profile    |
|----------|-----------------------|------|------------|
| Kibana   | `kibana:8.11.1`       | 5601 | `kibana`   |
| Graylog  | `graylog/graylog:5.2` | 9000 | `graylog`  |
| Logstash | `logstash:8.11.1`     | 5044 | `logstash` |

### Mail Testing

| Service  | Image                      | Port       | Profile    |
|----------|----------------------------|------------|------------|
| MailHog  | `mailhog/mailhog:v1.0.1`   | 1025, 8025 | `mailhog`  |
| Papercut | `jijiechen/papercut:5.7.0` | 25, 37408  | `papercut` |

### Job Scheduling

| Service | Image         | Port | Profile |
|---------|---------------|------|---------|
| Cron    | `php:8.5-cli` | -    | `cron`  |

## Quick Start

### Start Core Services

```bash
# Start with Nginx + PHP + PostgreSQL + Redis
docker compose --profile nginx --profile postgres --profile redis up -d

# Or use Makefile
make up PROFILES="nginx postgres redis"
```

### Start All Services

```bash
docker compose --profile all up -d
```

### Stop Services

```bash
docker compose down

# Or with Makefile
make down
```

## Container Configurations

### PHP (`containers/php/`)

- **Dockerfile** - PHP 8.5-FPM build configuration
- **php.ini** - PHP runtime configuration
- **msmtprc** - Mail sending configuration (SMTP relay)

Key PHP extensions installed:
- PDO (PostgreSQL, MySQL)
- Redis, Memcached
- AMQP, MongoDB
- OPcache, APCu
- Intl, GD, Imagick

### Nginx (`containers/nginx/`)

- **Dockerfile** - Nginx build configuration
- **site.conf** - Virtual host configuration with PHP-FPM proxy
- **ssl/** - Self-signed SSL certificates for local development

### Apache (`containers/apache/`)

- **Dockerfile** - Apache 2.4 build configuration
- **apache.conf** - Virtual host configuration
- **apache2.conf** - Main Apache configuration
- **ssl/** - SSL certificates

### PostgreSQL (`containers/postgres/`)

- **docker-entrypoint-initdb.d/** - Database initialization scripts

### Prometheus (`containers/prometheus/`)

- **prometheus.yml** - Metrics scraping configuration

### Grafana (`containers/grafana/`)

- **dashboard.json** - Pre-configured monitoring dashboard

### Logstash (`containers/logstash/`)

- **config/logstash.yml** - Logstash configuration
- **pipeline/main.conf** - Log processing pipeline

## Environment Configuration

The `.env` file contains 129 configuration parameters organized by service:

```ini
# Application
APP_ENV=dev
APP_DEBUG=true

# Database
DB_HOST=postgres
DB_PORT=5432
DB_NAME=app
DB_USER=app
DB_PASSWORD=secret

# Redis
REDIS_HOST=redis
REDIS_PORT=6379

# RabbitMQ
RABBITMQ_HOST=rabbitmq
RABBITMQ_PORT=5672
RABBITMQ_USER=guest
RABBITMQ_PASSWORD=guest

# ... and more
```

## Data Persistence

Persistent data is stored in `data/` directory:

```
data/
├── postgres_data/      # PostgreSQL database files
├── mysql_data/         # MySQL database files
├── redis/              # Redis persistence
├── mongodb/            # MongoDB data
├── elasticsearch/      # Elasticsearch indices
├── cassandra/          # Cassandra data
├── solr/               # Solr cores
└── rabbitmq/           # RabbitMQ data
```

## Scripts

### Build Script (`scripts/build.sh`)

Builds all Docker images:

```bash
./scripts/build.sh
```

## Cron Jobs

Scheduled tasks are defined in `cron/crontab`:

```cron
# Process outbox events every minute
* * * * * /usr/local/bin/php /var/www/html/bin/console outbox:process

# Clear expired cache daily
0 3 * * * /usr/local/bin/php /var/www/html/bin/console cache:pool:clear
```

## SSL Certificates

Self-signed certificates are provided for local HTTPS development:

- **Nginx**: `containers/nginx/ssl/nginx-selfsigned.crt`
- **Apache**: `containers/apache/ssl/server.crt`

For production, replace with valid certificates or use Let's Encrypt.

## Networking

All services are connected via Docker bridge network `app-network`:

- Services can communicate using container names as hostnames
- PHP container can reach `postgres`, `redis`, `rabbitmq`, etc.

## Health Checks

Built-in health check commands verify service connectivity:

```bash
# From host
docker compose exec php bin/console healthcheck:postgres
docker compose exec php bin/console healthcheck:redis
docker compose exec php bin/console healthcheck:rabbitmq
```

## Useful Commands

```bash
# View logs
docker compose logs -f php
docker compose logs -f nginx

# Enter PHP container
docker compose exec php bash

# Run Composer
docker compose exec php composer install

# Run migrations
docker compose exec php bin/console doctrine:migrations:migrate

# Clear cache
docker compose exec php bin/console cache:clear

# Restart specific service
docker compose restart php
```

## Profiles Reference

Use profiles to start only needed services:

| Profile         | Services                        |
|-----------------|---------------------------------|
| `nginx`         | Nginx web server                |
| `apache`        | Apache web server               |
| `php`           | PHP-FPM                         |
| `postgres`      | PostgreSQL                      |
| `mysql`         | MySQL                           |
| `redis`         | Redis                           |
| `memcached`     | Memcached                       |
| `rabbitmq`      | RabbitMQ                        |
| `kafka`         | Kafka + Zookeeper               |
| `elasticsearch` | Elasticsearch                   |
| `kibana`        | Kibana (requires elasticsearch) |
| `mongodb`       | MongoDB                         |
| `cassandra`     | Cassandra                       |
| `solr`          | Solr                            |
| `prometheus`    | Prometheus                      |
| `grafana`       | Grafana                         |
| `graylog`       | Graylog + MongoDB               |
| `logstash`      | Logstash                        |
| `zabbix`        | Zabbix Server + Web             |
| `mailhog`       | MailHog                         |
| `papercut`      | Papercut                        |
| `cron`          | Cron scheduler                  |

## Troubleshooting

### Port Conflicts

If ports are already in use, modify the `.env` file:

```ini
NGINX_PORT=8080
POSTGRES_PORT=5433
REDIS_PORT=6380
```

### Permission Issues

```bash
# Fix volume permissions
sudo chown -R 1000:1000 data/
```

### Container Won't Start

```bash
# Check logs
docker compose logs <service-name>

# Rebuild container
docker compose build --no-cache <service-name>
```

## License

See the main project LICENSE file.
