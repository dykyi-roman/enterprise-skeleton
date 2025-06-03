#-- Variables and Configuration --#
env = dev
workdir = ./infrastructure
compose-file = docker-compose.yml
compose-tools-file = docker-compose-tools.yml
php = es-php
network = es-network

# Color codes for output
YELLOW := \033[1;33m
GREEN := \033[1;32m
RED := \033[1;31m
BLUE := \033[1;34m
PURPLE := \033[1;35m
CYAN := \033[1;36m
WHITE := \033[1;37m
RESET := \033[0m

# Get configuration values
define get_config
	cd $(workdir) && grep -v '^[[:space:]]*[;#]' config/cs-config.ini | grep '=' | sed 's/[[:space:]]*#.*$$//' | cut -d'=' -f2 | tr -d ' ' | tr '\n' ',' | sed 's/,$$//'
endef
config = $(shell $(get_config))

# Print formatted message
define print_message
	@echo "$(CYAN)>>> $(1)$(RESET)"
endef

# Print success message
define print_success
	@echo "$(GREEN)✓ $(1)$(RESET)"
endef

# Print warning message
define print_warning
	@echo "$(YELLOW)⚠ $(1)$(RESET)"
endef

# Main goal - help output
.PHONY: help
help:
	@echo ""
	@echo "$(PURPLE)╔═════════════════════════════════════════════════════════════════════╗$(RESET)"
	@echo "$(PURPLE)║                   ENTERPRISE SKELETON MAKEFILE                      ║$(RESET)"
	@echo "$(PURPLE)╚═════════════════════════════════════════════════════════════════════╝$(RESET)"
	@echo ""
	@echo "$(YELLOW)Available commands:$(RESET)"
	@echo ""
	@echo "$(BLUE)▶ 🔧 CONFIGURATION$(RESET)"
	@grep -E '^[a-zA-Z0-9_-]+:.*## .*$$' $(MAKEFILE_LIST) | grep -E '(copy-config|show-config)' | sort | awk 'BEGIN {FS = ":.*?## "}{printf "  $(GREEN)%-30s$(RESET) %s\n", $$1, $$2}'
	@echo ""
	@echo "$(BLUE)▶ 🐳 DOCKER MANAGEMENT$(RESET)"
	@grep -E '^[a-zA-Z0-9_-]+:.*## .*$$' $(MAKEFILE_LIST) | grep -E '(install|up|down|start|stop|restart|build|prune|enter|console)' | sort | awk 'BEGIN {FS = ":.*?## "}{printf "  $(GREEN)%-30s$(RESET) %s\n", $$1, $$2}'
	@echo ""
	@echo "$(BLUE)▶ 📋 LOGS$(RESET)"
	@grep -E '^[a-zA-Z0-9_-]+:.*## .*$$' $(MAKEFILE_LIST) | grep -E '(logs)' | sort | awk 'BEGIN {FS = ":.*?## "}{printf "  $(GREEN)%-30s$(RESET) %s\n", $$1, $$2}'
	@echo ""
	@echo "$(BLUE)▶ 🧪 CODE QUALITY & TESTING$(RESET)"
	@grep -E '^[a-zA-Z0-9_-]+:.*## .*$$' $(MAKEFILE_LIST) | grep -E '(phpcs|phpstan|test|psalm|composer|deptrac|cc|ci|phpmetrics)' | sort | awk 'BEGIN {FS = ":.*?## "}{printf "  $(GREEN)%-30s$(RESET) %s\n", $$1, $$2}'
	@echo ""
	@echo "$(BLUE)▶ 🔄 FRAMEWORK SELECTION$(RESET)"
	@grep -E '^[a-zA-Z0-9_-]+:.*## .*$$' $(MAKEFILE_LIST) | grep -E '(framework)' | sort | awk 'BEGIN {FS = ":.*?## "}{printf "  $(GREEN)%-30s$(RESET) %s\n", $$1, $$2}'
	@echo ""
	@echo "$(BLUE)▶ 📚 DOCUMENTATION$(RESET)"
	@grep -E '^[a-zA-Z0-9_-]+:.*## .*$$' $(MAKEFILE_LIST) | grep -E '(swagger)' | sort | awk 'BEGIN {FS = ":.*?## "}{printf "  $(GREEN)%-30s$(RESET) %s\n", $$1, $$2}'
	@echo ""
	@echo "$(BLUE)▶ 🔄 DATABASE MIGRATIONS$(RESET)"
	@grep -E '^[a-zA-Z0-9_-]+:.*## .*$$' $(MAKEFILE_LIST) | grep -E '(migration)' | sort | awk 'BEGIN {FS = ":.*?## "}{printf "  $(GREEN)%-30s$(RESET) %s\n", $$1, $$2}'
	@echo ""
	@echo "$(CYAN)Example: make up$(RESET)"
	@echo ""

## 🔧 CONFIGURATION
.PHONY: copy-config show-config

copy-config: ## Copy cs-config.ini.dist to cs-config.ini
	@cp infrastructure/config/cs-config.ini.dist infrastructure/config/cs-config.ini
	$(call print_success,"Configuration file successfully copied")

show-config: ## Display current configuration
	$(call print_message,"Current configuration:")
	@echo "$(GREEN)$(config)$(RESET)"

## 🐳 DOCKER MANAGEMENT
.PHONY: install up down start stop restart build prune enter console

install: ## Install project dependencies and set up Docker environment
	$(call print_message,"Creating Docker network (if not exists)...")
	@docker network inspect $(network) --format {{.Id}} 2>/dev/null || docker network create $(network)
	$(call print_message,"Starting containers...")
	$(MAKE) up
	$(call print_message,"Installing Composer dependencies...")
	@docker exec -it $(php) bash -c "composer install"
	$(call print_success,"Installation completed successfully!")

up: ## Start Docker containers
	$(call print_message,"Starting Docker containers...")
	@if cd $(workdir) && ./scripts/build.sh $(config); then \
    		export COMPOSE_PROFILES="$(config)" && \
			docker compose -f $(compose-file) -f $(compose-tools-file) build php && \
			docker compose -f $(compose-file) -f $(compose-tools-file) up -d; \
    	else \
    		export COMPOSE_PROFILES="$(config)" && \
            docker compose -f $(compose-file) -f $(compose-tools-file) up -d; \
    fi
	$(call print_success,"Docker containers started")

down: ## Stop Docker containers
	$(call print_message,"Stopping Docker containers...")
	@cd $(workdir) && COMPOSE_PROFILES="$(config)" docker compose -f $(compose-file) -f $(compose-tools-file) down --remove-orphans
	$(call print_success,"Docker containers stopped")

start: up ## Alias for 'up' command

stop: down ## Alias for 'down' command

restart: ## Restart Docker containers
	$(call print_message,"Restarting Docker containers...")
	@export COMPOSE_PROFILES="$(config)" && cd $(workdir) && docker compose -f $(compose-file) -f $(compose-tools-file) restart
	$(call print_success,"Docker containers restarted")

build: ## Build specific container (usage: make build php)
	$(call print_message,"Building container $(filter-out $@,$(MAKECMDGOALS))...")
	@export COMPOSE_PROFILES="$(config)" && cd $(workdir) && docker compose -f $(compose-file) -f $(compose-tools-file) up -d --build $(filter-out $@,$(MAKECMDGOALS))
	$(call print_success,"Container build completed")

prune: ## Remove all Docker containers, volumes, and networks
	$(call print_warning,"Removing all Docker containers, volumes, and networks...")
	@export COMPOSE_PROFILES="$(config)" && cd $(workdir) && docker compose -f $(compose-file) -f $(compose-tools-file) down -v --remove-orphans --rmi all
	@cd $(workdir) && docker network remove $(network)
	$(call print_success,"All Docker resources removed")

enter: ## Enter PHP container shell
	$(call print_message,"Entering PHP container...")
	@docker exec -it $(php) sh

console: ## Execute Symfony console commands (usage: make console command="your:command")
	$(call print_message,"Executing command: $(filter-out $@,$(MAKECMDGOALS))")
	@docker exec -it $(php) bash -c "php bin/console $(filter-out $@,$(MAKECMDGOALS))"

## 📋 LOGS
.PHONY: logs-cron logs-php

logs-cron: ## View cron output logs
	$(call print_message,"Viewing cron logs...")
	@docker exec es-cron tail -f /var/log/cron.log

logs-php: ## View PHP logs
	$(call print_message,"Viewing PHP logs...")
	@tail -f code/var/log/$(env)-$(shell date +%Y-%m-%d).log

## 🧪 CODE QUALITY & TESTING
.PHONY: phpcs phpstan test-php test-postman deptrac psalm composer-require-checker composer-outdated phpmetrics cc ci

phpcs: ## Run PHP CS Fixer to fix code style
	$(call print_message,"Running PHP CS Fixer...")
	@docker exec -it $(php) bash -c "cd /var/www/html/code && PHP_CS_FIXER_IGNORE_ENV=1 php vendor/bin/php-cs-fixer fix -v --using-cache=no --config=../tools/.php-cs-fixer.php"
	$(call print_success,"PHP CS Fixer completed!")

phpstan: ## Run PHPStan for static code analysis
	$(call print_message,"Running PHPStan...")
	@docker exec -it $(php) bash -c "cd /var/www/html/code && php vendor/bin/phpstan analyse src --configuration=../tools/phpstan.neon"
	$(call print_success,"PHPStan completed!")

test-php: ## Run PHPUnit tests
	$(call print_message,"Running PHPUnit tests...")
	@docker exec -it $(php) bash -c "cd /var/www/html/code && APP_ENV=test php vendor/bin/phpunit -c ../tools/phpunit.xml.dist"
	$(call print_success,"PHP tests completed!")

test-postman: ## Run Postman collection tests using Newman
	$(call print_message,"Running Postman tests...")
	@cd $(workdir) && COMPOSE_PROFILES="newman,php,nginx" docker compose -f $(compose-file) -f $(compose-tools-file) run --rm newman
	$(call print_success,"Postman tests completed!")

deptrac: ## Check dependencies between domains (no cache)
	$(call print_message,"Running dependency check between domains...")
	@docker exec -it $(php) bash -c "cd /var/www/html/code && php vendor/bin/deptrac analyse --config-file=../tools/deptrac/deptrac-domain.yaml --no-cache | grep -v 'Uncovered'"
	@docker exec -it $(php) bash -c "cd /var/www/html/code && php vendor/bin/deptrac analyse --config-file=../tools/deptrac/deptrac-layers.yaml --no-cache | grep -v 'Uncovered'"
	$(call print_success,"Deptrac completed!")

psalm: ## Run Psalm static analysis (no cache)
	$(call print_message,"Running Psalm...")
	@docker exec -it $(php) bash -c "cd /var/www/html/code && php vendor/bin/psalm --config=../tools/psalm.xml --no-cache"
	$(call print_success,"Psalm completed!")

composer-require-checker: ## Check composer dependencies
	$(call print_message,"Checking composer dependencies...")
	@docker exec -it $(php) bash -c "cd /var/www/html/code && php vendor/bin/composer-require-checker check --config-file=../tools/composer-require-checker.json composer.json"
	$(call print_success,"Composer dependencies check completed!")

composer-outdated: ## Check outdated composer dependencies
	$(call print_message,"Checking outdated composer dependencies...")
	@docker exec -it $(php) bash -c "cd /var/www/html/code && composer outdated --direct --major-only --ignore-platform-req=php"
	$(call print_success,"Outdated composer dependencies check completed!")

phpmetrics: ## Generate PHP Metrics report
	$(call print_message,"Generating PHP Metrics report...")
	@docker exec -it $(php) bash -c 'cd /var/www/html/code && COMPOSER_VENDOR_DIR=vendor php -d memory_limit=1G vendor/bin/phpmetrics --report-html=docs/metrics src/'
	$(call print_success,"PHP Metrics report created in code/docs/metrics directory!")

cc: ## Clear cache
	$(call print_message,"Clearing cache...")
	@docker exec -it $(php) bash -c "composer dumpautoload -a"
	@docker exec -it $(php) bash -c "php bin/console c:c"
	$(call print_success,"Cache successfully cleared!")

ci: ## Run all code quality checks
	$(call print_message,"Running full code quality check...")
	$(MAKE) phpcs
	$(MAKE) swagger-generate
	$(MAKE) phpstan
	$(MAKE) psalm
	$(MAKE) deptrac
	$(MAKE) composer-require-checker
	$(MAKE) test-php
	$(call print_success,"All code quality checks completed!")

## 🔄 FRAMEWORK SELECTION
.PHONY: framework

framework: ## Switch to specific framework branch (usage: make framework symfony|laravel)
	$(call print_message,"Switching to branch $(filter-out $@,$(MAKECMDGOALS))...")
	@git checkout $(filter-out $@,$(MAKECMDGOALS));
	$(call print_success,"Switch to $(filter-out $@,$(MAKECMDGOALS)) completed!")

## 📚 DOCUMENTATION
.PHONY: swagger-generate

swagger-generate: ## Generate OpenAPI/Swagger documentation
	$(call print_message,"Generating OpenAPI/Swagger documentation...")
	@docker exec -it $(php) bash -c "php vendor/bin/openapi src --output docs/api/openapi.yaml --format yaml"
	$(call print_success,"OpenAPI documentation generated in code/docs/api/openapi.yaml")

## 🔄 DATABASE MIGRATIONS
.PHONY: migration-create migration-run

migration-create: ## Create a new migration (usage: make migration-create)
	$(call print_message,"Creating new migration...")
	@docker exec -it $(php) bash -c "php bin/console doctrine:migrations:diff"
	$(call print_success,"Migration created!")

migration-run: ## Run all pending migrations
	$(call print_message,"Running migrations...")
	@docker exec -it $(php) bash -c "php bin/console doctrine:migrations:migrate --no-interaction"
	$(call print_success,"Migrations completed!")

# This is required to handle arguments in make commands
%:
	@: