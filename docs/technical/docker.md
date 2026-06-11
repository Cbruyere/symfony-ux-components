# Docker Guidelines

## Purpose

This repository follows a Docker-first approach.

The application must be fully operational through Docker.

Developers should not be required to install PHP, Composer, Node.js or PostgreSQL directly on their
host machine.

All project operations must be executable through Docker containers.

---

## Docker First Rule

Always use Docker.

Never ask developers to run:

```bash
php
composer
symfony
npm
yarn
pnpm
```

directly on the host machine.

Use Makefile commands instead.

---

## Infrastructure Overview

Expected containers:

```text
app
postgres
mailcatcher
gotenberg
```

Optional:

```text
node
```

if separated from the PHP container.

---

## Application Container

The application container is responsible for:

- PHP execution
- Composer
- Symfony CLI
- PHPUnit
- PHPStan
- Linters
- Node.js tooling

The container should be the primary development environment.

---

## PHP Version

Mandatory version:

```text
PHP 8.4
```

Container type:

```text
PHP-FPM
```

---

## PostgreSQL

Database:

```text
PostgreSQL
```

Use the latest stable version available.

Port mapping:

```text
127.0.0.1:5432:5432
```

Requirements:

- persistent volume
- automatic initialization
- support for tests

---

## Mailcatcher

Mailcatcher is mandatory.

Purpose:

- local email testing
- password reset testing
- notification testing

No external SMTP service should be required.

---

## Gotenberg

Gotenberg is mandatory.

Purpose:

- PDF generation
- HTML to PDF conversion

Expected service name:

```text
gotenberg
```

The application should communicate with Gotenberg through a dedicated service.

Example:

```text
PdfGeneratorService
```

---

## UID / GID

The local developer user is:

```text
1000:1000
```

Dockerfiles must support:

```dockerfile
ARG UID=1000
ARG GID=1000
```

Purpose:

- avoid permissions issues
- avoid root-owned files
- ensure cache accessibility
- ensure Composer accessibility
- ensure npm accessibility

---

## Writable Directories

The following directories must remain writable:

```text
var/
var/cache/
var/log/
vendor/
node_modules/
```

Generated files must remain editable by the local user.

---

## Volumes

Expected persistent volumes:

```text
postgres_data
```

Optional:

```text
composer_cache
npm_cache
```

---

## Docker Compose

The project should use:

```text
compose.yaml
```

Optional:

```text
compose.override.yaml
```

Avoid maintaining multiple Compose configurations without justification.

---

## Environment Variables

Sensitive values must never be hardcoded.

Use:

```text
.env
.env.local
.env.test
```

Provide:

```text
.env.example
```

for onboarding.

---

## Makefile

The Makefile is the primary entry point.

All recurring commands must be available through Make targets.

---

## Mandatory Targets

### Infrastructure

```bash
make build
make up
make down
make restart
make logs
```

---

### Shell Access

```bash
make shell
```

Purpose:

Open a shell in the application container.

---

### Composer

```bash
make composer-install
make composer-update
make composer-require
```

Composer must run inside Docker.

Bad:

```bash
composer install
```

Good:

```bash
make composer-install
```

---

### NPM

```bash
make npm-install
make npm-build
make npm-dev
```

---

### Doctrine

```bash
make db-create
make db-migrate
make db-diff
make fixtures
```

---

### Testing

```bash
make test
```

---

### Static Analysis

```bash
make phpstan
```

---

### Linters

```bash
make lint
make lint-php
make lint-twig
```

---

## XDebug

XDebug must be enabled.

Purpose:

- debugging
- breakpoints
- profiling

---

## VSCode

The repository must provide:

```text
docs/vscode-xdebug.md
```

This file must contain:

- extension requirements
- launch.json example
- path mappings

---

## Debug Port

Default port:

```text
9003
```

---

## Example Path Mapping

Container:

```text
/var/www/html
```

Host:

```text
${workspaceFolder}
```

---

## Symfony Commands

Symfony commands must run inside Docker.

Bad:

```bash
php bin/console cache:clear
```

Good:

```bash
make console CMD="cache:clear"
```

---

## NPM Commands

NPM commands must run inside Docker.

Bad:

```bash
npm install
```

Good:

```bash
make npm-install
```

---

## Database Migrations

All schema changes must use Doctrine migrations.

Never modify the database manually.

Expected workflow:

```bash
make db-diff
make db-migrate
```

---

## Fixtures

Fixtures should provide a working development environment.

Minimum fixtures:

```text
Admin User
Regular User
```

Default password:

```text
rt0zclln
```

Fixtures must be executable through:

```bash
make fixtures
```

---

## Test Environment

A dedicated test environment must exist.

Expected file:

```text
.env.test
```

Tests must not use the development database.

---

## CI Compatibility

All commands available locally through Makefile must be executable in CI.

CI should be able to execute:

```bash
make test
make phpstan
make lint
```

without modifications.

---

## Docker Completion Checklist

Before validating infrastructure changes:

- Containers start successfully
- Database is reachable
- Mailcatcher is reachable
- Gotenberg is reachable
- XDebug works
- PHPUnit passes
- PHPStan passes
- Linters pass
- No permission issues
- UID/GID respected

Infrastructure must remain reproducible across machines.
