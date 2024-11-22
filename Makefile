workdir = ./docker
config = docker-compose.yml
php = es-php
db = postgres
network = es-network

help:
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## -- Docker Commands --

install: ## Install project dependencies and set up Docker environment
	docker network inspect $(network) --format {{.Id}} 2>/dev/null || docker network create $(network)
	export COMPOSE_PROFILES=$(db) && cd $(workdir) && docker compose -f $(config) up -d
	docker exec -it $(php) bash -c "composer install"

up: ## Start Docker containers
	export COMPOSE_PROFILES=$(db) && cd $(workdir) && docker compose -f $(config) up -d

down: ## Stop Docker containers
	export COMPOSE_PROFILES=$(db) && cd $(workdir) && docker compose -f $(config) down

start: up ## Alias for 'up' command

stop: down ## Alias for 'down' command

restart: ## Restart Docker containers
	export COMPOSE_PROFILES=$(db) && cd $(workdir) && docker compose -f $(config) restart

prune: ## Remove all Docker containers, volumes, and networks
	export COMPOSE_PROFILES=$(db) && cd $(workdir) && docker compose -f $(config) down -v --remove-orphans --rmi all
	cd $(workdir) && docker network remove $(network)

enter: ## Enter PHP container shell
	docker exec -it $(php) sh

console: ## Execute Symfony console commands (usage: make console command="your:command")
	docker exec -it $(php) bash -c "php bin/console $(filter-out $@,$(MAKECMDGOALS))"

## -- Code Quality & Testing --

phpcs: ## Run PHP CS Fixer to fix code style
	docker exec -it $(php) bash -c "cd /var/www/html/code && php vendor/bin/php-cs-fixer fix -v --using-cache=no --config=../tools/.php-cs-fixer.php"
	@echo "phpcs done"

phpstan: ## Run PHPStan for static code analysis
	docker exec -it $(php) bash -c "cd /var/www/html/code && php vendor/bin/phpstan analyse src --configuration=../tools/phpstan.neon"

test-run: ## Run PHPUnit tests
	docker exec -it $(php) bash -c "cd /var/www/html/code && APP_ENV=test php vendor/bin/phpunit -c ../tools/phpunit.xml.dist"
	@echo "Test done!"

deptrac: ## Check dependencies between domains (no cache)
	docker exec -it $(php) bash -c "cd /var/www/html/code && php vendor/bin/deptrac analyse --config-file=../tools/deptrac-domain.yaml --no-cache --report-uncovered"
	docker exec -it $(php) bash -c "cd /var/www/html/code && php vendor/bin/deptrac analyse --config-file=../tools/deptrac-layers.yaml --no-cache --report-uncovered"

psalm: ## Run Psalm static analysis (no cache)
	docker exec -it $(php) bash -c "cd /var/www/html/code && php vendor/bin/psalm --config=../tools/psalm.xml --no-cache"

ci: ## Run all code quality checks
	$(MAKE) phpcs
	$(MAKE) phpstan
	$(MAKE) psalm
	$(MAKE) deptrac
	$(MAKE) test-run

## -- Database Migrations --

migration-create: ## Create a new migration (usage: make migration-create)
	docker exec -it $(php) bash -c "cd /var/www/html/code && php bin/console doctrine:migrations:diff"

migration-run: ## Run all pending migrations
	docker exec -it $(php) bash -c "cd /var/www/html/code && php bin/console doctrine:migrations:migrate --no-interaction"