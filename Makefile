#-- Variables --
workdir = ./infrastructure
compose-file = docker-compose.yml
compose-tools-file = docker-compose-tools.yml
php = es-php
network = es-network

# Get the config values
define get_config
	cd $(workdir) && sed -n 's/^\([^#]*=\)\([^#]*\).*/\2/p' config/cs-config | tr -d ' ' | tr '\n' ',' | sed 's/,$$//'
endef
config = $(shell $(get_config))

help:
	@grep -E '(^[a-zA-Z0-9_-]+:.*?## .*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## -- Config --

copy-config: ## Copy cs-config.dist to cs-config file
	@cp infrastructure/config/cs-config.dist infrastructure/config/cs-config
	@echo "Configuration file copied successfully"

show-config: ## Display current configuration
	@echo "Current configuration: $(config)"

## -- Docker Commands --

install: copy-config ## Install project dependencies and set up Docker environment
	docker network inspect $(network) --format {{.Id}} 2>/dev/null || docker network create $(network)
	$(MAKE) up
	docker exec -it $(php) bash -c "composer install"

up: ## Start Docker containers
	export COMPOSE_PROFILES="$(config)" && cd $(workdir) && docker compose -f $(compose-file) -f $(compose-tools-file) up -d

down: ## Stop Docker containers
	cd $(workdir) && COMPOSE_PROFILES="$(config)" docker compose -f $(compose-file) -f $(compose-tools-file) down --remove-orphans

start: up ## Alias for 'up' command

stop: down ## Alias for 'down' command

restart: ## Restart Docker containers
	export COMPOSE_PROFILES="$(config)" && cd $(workdir) && docker compose -f $(compose-file) -f $(compose-tools-file) restart

build: ## Build specific container (usage: make build php)
	export COMPOSE_PROFILES="$(config)" && cd $(workdir) && docker compose -f $(compose-file) -f $(compose-tools-file) up -d --build $(filter-out $@,$(MAKECMDGOALS))

prune: ## Remove all Docker containers, volumes, and networks
	export COMPOSE_PROFILES="$(config)" && cd $(workdir) && docker compose -f $(compose-file) -f $(compose-tools-file) down -v --remove-orphans --rmi all
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

test: ## Run PHPUnit tests
	docker exec -it $(php) bash -c "cd /var/www/html/code && APP_ENV=test php vendor/bin/phpunit -c ../tools/phpunit.xml.dist"
	@echo "Test done!"

deptrac: ## Check dependencies between domains (no cache)
	docker exec -it $(php) bash -c "cd /var/www/html/code && php vendor/bin/deptrac analyse --config-file=../tools/deptrac-domain.yaml --no-cache --report-uncovered"
	docker exec -it $(php) bash -c "cd /var/www/html/code && php vendor/bin/deptrac analyse --config-file=../tools/deptrac-layers.yaml --no-cache --report-uncovered"
	@echo "Deptrac done!"

psalm: ## Run Psalm static analysis (no cache)
	docker exec -it $(php) bash -c "cd /var/www/html/code && php vendor/bin/psalm --config=../tools/psalm.xml --no-cache"
	@echo "Psalm done!"

ci: ## Run all code quality checks
	$(MAKE) phpcs
	$(MAKE) swagger-generate
	$(MAKE) phpstan
	$(MAKE) psalm
	$(MAKE) deptrac
	$(MAKE) test-run

## -- Documentation --

swagger-generate: ## Generate OpenAPI/Swagger documentation
	docker exec -it $(php) bash -c "php vendor/bin/openapi src --output docs/api/openapi.yaml --format yaml"
	@echo "OpenAPI documentation generated in code/docs/api/openapi.yaml"

## -- Database Migrations --

migration-create: ## Create a new migration (usage: make migration-create)
	docker exec -it $(php) bash -c "php bin/console doctrine:migrations:diff"

migration-run: ## Run all pending migrations
	docker exec -it $(php) bash -c "php bin/console doctrine:migrations:migrate --no-interaction"

# This is required to handle arguments in make commands
%:
	@: