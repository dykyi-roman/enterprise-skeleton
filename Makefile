workdir = ./docker
config = docker-compose.yml
php = es-php
network = es-network

help:
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

install: ## install
	docker network inspect $(network) --format {{.Id}} 2>/dev/null || docker network create $(network)
	cd $(workdir) && docker compose -f $(config) up -d
	docker exec -it $(php) bash -c "composer install"

up: ## up
	cd $(workdir) && docker compose -f $(config) up -d

down: ## down
	cd $(workdir) && docker compose -f $(config) down

start: up ## start

stop: down ## stop

restart: ## restart
	cd $(workdir) && docker compose -f $(config) restart

prune: ## prune
	cd $(workdir) && docker compose -f $(config) down -v --remove-orphans --rmi all
	cd $(workdir) && docker network remove $(network)

enter: ## enter to container
	docker exec -it $(php) sh

console: ## console command
	docker exec -it $(php) bash -c "php bin/console $(filter-out $@,$(MAKECMDGOALS))"

## --

phpcs: ## phpcs
	docker exec -it $(php) bash -c "php vendor/bin/php-cs-fixer fix -v --using-cache=no --config=./config/.php-cs-fixer.php"
	@echo "phpcs done"

phpstan: ## phpstan
	docker exec -it $(php) bash -c "php vendor/bin/phpstan analyse src --configuration=./config/phpstan.neon"

test-run: ## tests
	docker exec -it $(php) bash -c "php vendor/bin/codecept build -c ./config/codeception.yml && php vendor/bin/codecept run -c ./config/codeception.yml $(filter-out $@,$(MAKECMDGOALS))"
	@echo "Test done!"

ci: ## CI
	$(MAKE) phpcs
	$(MAKE) phpstan
	$(MAKE) test-run