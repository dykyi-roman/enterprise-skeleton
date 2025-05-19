#-- Variables --
env = dev
workdir = ./infrastructure
compose-file = docker-compose.yml
compose-tools-file = docker-compose-tools.yml
php = es-php
network = es-network

# Get the config values
define get_config
	cd $(workdir) && grep -v '^[[:space:]]*[;#]' config/cs-config.ini | grep '=' | sed 's/[[:space:]]*#.*$$//' | cut -d'=' -f2 | tr -d ' ' | tr '\n' ',' | sed 's/,$$//'
endef
config = $(shell $(get_config))

help:
	@grep -E '(^[a-zA-Z0-9_-]+:.*?## .*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## -- Config --

copy-config: ## Copy cs-config.ini.dist to cs-config file
	@cp infrastructure/config/cs-config.ini.dist infrastructure/config/cs-config.ini
	@echo "Configuration file copied successfully"

show-config: ## Display current configuration
	@echo "Current configuration: $(config)"

## -- Docker Commands --

install: ## Install project dependencies and set up Docker environment
	docker network inspect $(network) --format {{.Id}} 2>/dev/null || docker network create $(network)
	$(MAKE) up
	docker exec -it $(php) bash -c "composer install"

up: ## Start Docker containers
	@if cd $(workdir) && ./scripts/build.sh $(config); then \
    		export COMPOSE_PROFILES="$(config)" && \
			docker compose -f $(compose-file) -f $(compose-tools-file) build php && \
			docker compose -f $(compose-file) -f $(compose-tools-file) up -d; \
    	else \
    		export COMPOSE_PROFILES="$(config)" && \
            docker compose -f $(compose-file) -f $(compose-tools-file) up -d; \
    fi

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

## -- Logs --
logs-cron: ## View cron output logs
	docker exec es-cron tail -f /var/log/cron.log

logs-php: ## View PHP logs
	tail -f code/var/log/$(env)-$(shell date +%Y-%m-%d).log

## -- Code Quality & Testing --

phpcs: ## Run PHP CS Fixer to fix code style
	docker exec -it $(php) bash -c "cd /var/www/html/code && PHP_CS_FIXER_IGNORE_ENV=1 php vendor/bin/php-cs-fixer fix -v --using-cache=no --config=../tools/.php-cs-fixer.php"
	@echo "PHP CS Fixer done!"

phpstan: ## Run PHPStan for static code analysis
	docker exec -it $(php) bash -c "cd /var/www/html/code && php vendor/bin/phpstan analyse src --configuration=../tools/phpstan.neon"
	@echo "Phpstan done!"

test-php: ## Run PHPUnit tests
	docker exec -it $(php) bash -c "cd /var/www/html/code && APP_ENV=test php vendor/bin/phpunit -c ../tools/phpunit.xml.dist"
	@echo "Php Test done!"

test-postman: ## Run Postman collection tests using Newman
	cd $(workdir) && COMPOSE_PROFILES="newman,php,nginx" docker compose -f $(compose-file) -f $(compose-tools-file) run --rm newman
	@echo "Postman Test done!"

deptrac: ## Check dependencies between domains (no cache)
	docker exec -it $(php) bash -c "cd /var/www/html/code && php vendor/bin/deptrac analyse --config-file=../tools/deptrac/deptrac-domain.yaml --no-cache | grep -v 'Uncovered'"
	docker exec -it $(php) bash -c "cd /var/www/html/code && php vendor/bin/deptrac analyse --config-file=../tools/deptrac/deptrac-layers.yaml --no-cache | grep -v 'Uncovered'"
	@echo "Deptrac done!"

psalm: ## Run Psalm static analysis (no cache)
	docker exec -it $(php) bash -c "cd /var/www/html/code && php vendor/bin/psalm --config=../tools/psalm.xml --no-cache"
	@echo "Psalm done!"

composer-require-checker: ## Check composer dependencies
	docker exec -it $(php) bash -c "cd /var/www/html/code && php vendor/bin/composer-require-checker check --config-file=../tools/composer-require-checker.json composer.json"
	@echo "Composer Require Checker done!"

composer-outdated: ## Check composer outdated
	docker exec -it $(php) bash -c "cd /var/www/html/code && composer outdated --direct --major-only --ignore-platform-req=php"
	@echo "Composer Outdated Checker done!"

phpmetrics: ## Generate PHP Metrics report
	docker exec -it $(php) bash -c 'cd /var/www/html/code && COMPOSER_VENDOR_DIR=vendor php -d memory_limit=1G vendor/bin/phpmetrics --report-html=docs/metrics src/'
	@echo "PHP Metrics report generated in code/docs/metrics directory!"

cc: ## clear cache
	docker exec -it $(php) bash -c "composer dumpautoload -a"
	docker exec -it $(php) bash -c "php bin/console c:c"

ci: ## Run all code quality checks
	$(MAKE) phpcs
	$(MAKE) swagger-generate
	$(MAKE) phpstan
	$(MAKE) psalm
	$(MAKE) deptrac
	$(MAKE) composer-require-checker
	$(MAKE) test-php

## -- Framework Selection --

framework: ## Switch to specific framework branch (usage: make framework symfony|laravel)
	git checkout $(filter-out $@,$(MAKECMDGOALS)); \

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