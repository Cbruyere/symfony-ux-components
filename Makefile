UID := $(shell id -u)
GID := $(shell id -g)
COMPOSE := UID=$(UID) GID=$(GID) docker compose
PHP := $(COMPOSE) exec php
PHP_RUN := $(COMPOSE) run --rm php_run


.PHONY: help install tests phpstan lint-twig lint-md npm-build lint-php lint composer-install npm-install composer-require

help:
	@grep -E '^[a-zA-Z_-]+:.*?## ' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-18s\033[0m %s\n", $$1, $$2}'

build: ## Build Docker images
	$(COMPOSE) build

start: ## Start the stack
	$(COMPOSE) up -d --remove-orphans

stop: ## Stop containers
	$(COMPOSE) stop

down: ## Stop and remove containers
	$(COMPOSE) down

install: ## install dependencies
	$(PHP) composer install
	$(PHP) npm install

composer-install: ## Run composer install
	$(PHP) composer install

composer-require: ## Require Composer package, usage: make composer-require ARGS="vendor/package"
	$(PHP) composer require $(ARGS)

npm-install: ## Run npm install
	$(PHP) npm install

npm-build: ## run npm build
	$(PHP) npm run build

tests: ## run all tests
	$(PHP_RUN) vendor/bin/phpunit

phpstan: ## Run phpstan
	$(PHP_RUN) vendor/bin/phpstan analyse --memory-limit=512M

lint: lint-php lint-twig ## Run all coding linters

lint-twig: ## twig linter
	$(PHP_RUN) php demo/bin/console lint:twig templates

lint-md: ## markdown linter
	$(PHP_RUN) npm run lint:md

lint-php: ## Lint PHP files
	$(PHP_RUN) find src tests config -name '*.php' -print0 | xargs -0 -r php -l

fix-md: ## Fix all markdown errors
	$(PHP_RUN) npx prettier --write docs/**/*.md

db-create: ## Create the configured database if missing
	$(PHP) php bin/console doctrine:database:create --if-not-exists

db-migrate: ## Run Doctrine migrations
	$(PHP) php bin/console doctrine:migrations:migrate --no-interaction

db-diff: ## Generate a Doctrine migration diff
	$(PHP) php bin/console doctrine:migrations:diff

db-status: ## Show Doctrine migration status
	$(PHP) php bin/console doctrine:migrations:status

fixtures: ## Load Doctrine fixtures
	$(PHP) php bin/console doctrine:fixtures:load --no-interaction

console: ## run symfony console
	$(PHP) php bin/console

factory: ## Create a new fixture factory
	$(PHP) php bin/console make:factory

story: ## Create a new fixture story	
	$(PHP) php bin/console make:story

load-story: ## load fixtures story	
	$(PHP) php bin/console foundry:load-stories
