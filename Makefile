PHP=docker compose run --rm php

.PHONY: help install test phpstan lint-twig lint-md npm-build lint-php lint composer-install npm-install composer-require

help:
	@grep -E '^[a-zA-Z_-]+:.*?## ' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-18s\033[0m %s\n", $$1, $$2}'

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

test: ## run all tests
	$(PHP) vendor/bin/phpunit

phpstan: ## Run phpstan
	$(PHP) vendor/bin/phpstan analyse

lint: lint-php lint-twig ## Run all coding linters

lint-twig: ## twig linter
	$(PHP) php demo/bin/console lint:twig templates

lint-md: ## markdown linter
	$(PHP) npm run lint:md

lint-php: ## Lint PHP files
	$(PHP) find src tests public config -name '*.php' -print0 | xargs -0 -r php -l

fix-md: ## Fix all markdown errors
	$(PHP_RUN) npx prettier --write docs/**/*.md
