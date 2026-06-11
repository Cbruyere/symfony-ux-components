# Testing Guidelines

## Purpose

This document defines the testing strategy used by the Symfony UX Starter Kit.

The goal is to ensure:

- reliability
- maintainability
- consistency
- readability

All tests must follow these conventions.

---

# Testing Stack

Mandatory tools:

- PHPUnit
- Symfony Test Framework
- Doctrine Test Environment

Optional future additions:

- Infection
- Panther
- Behat

---

# Test Types

The starter kit contains:

## Functional Tests

Purpose:

Validate application behavior through HTTP requests.

Examples:

- home page
- login page
- authenticated pages

Location:

```text
tests/Functional/
```

---

## Unit Tests

Purpose:

Validate isolated PHP logic.

Location:

```text
tests/Unit/
```

---

# Abstract Test Base

All functional tests must inherit from a shared abstract class.

Location:

```text
tests/AbstractWebTestCase.php
```

Purpose:

Centralize common utilities.

Examples:

- createUser()
- loginUser()
- refreshDatabase()
- createAuthenticatedClient()

---

# Functional Test Structure

Good:

```php
final class HomeControllerTest extends AbstractWebTestCase
{
    public function testHomePageIsAccessible(): void
    {
    }
}
```

Bad:

```php
final class HomeControllerTest extends WebTestCase
{
}
```

Always use the shared base class.

---

# Database Isolation

Tests must never depend on:

- developer data
- existing database state
- manually inserted data

Tests must prepare their own data.

---

# Fixtures

Fixtures should be reusable.

Fixtures must remain generic.

Examples:

```text
Admin User
Regular User
```

Avoid business fixtures in the starter.

Bad:

```text
CustomerFixture
InvoiceFixture
ProductFixture
```

---

# Test User Creation

User creation should be centralized.

Expected helper:

```php
protected function createUser(
    string $email = 'user@example.com'
): User
```

Purpose:

Avoid duplicate setup code.

---

# Authenticated Client

Authentication should be centralized.

Expected helper:

```php
protected function createAuthenticatedClient(): KernelBrowser
```

Purpose:

Avoid repetitive login code.

---

# Login Helper

Expected helper:

```php
protected function loginUser(
    User $user
): KernelBrowser
```

All tests should reuse this method.

---

# Refresh Database

Expected helper:

```php
protected function refreshDatabase(): void
```

Purpose:

Reset database state between tests.

Implementation may vary depending on future tooling.

---

# Static Factory Methods

Common factories should be preferred.

Example:

```php
UserFactory::create();
```

Avoid duplicating entity initialization.

---

# Naming Conventions

Good:

```php
testHomePageIsAccessible()
testAuthenticatedUserCanAccessAccount()
testAnonymousUserIsRedirectedToLogin()
```

Bad:

```php
test1()
testPage()
testAccount()
```

Names should describe behavior.

---

# Assertion Strategy

Assertions should be explicit.

Good:

```php
self::assertResponseIsSuccessful();
```

```php
self::assertSelectorTextContains(
    'h1',
    'Home'
);
```

Bad:

```php
self::assertTrue(true);
```

---

# Home Page Tests

Minimum coverage:

## Home Accessible

```text
GET /home
```

Expected:

```text
200 OK
```

---

## Home Content

Verify presence of:

- cards
- navigation
- example components

---

# Login Tests

Minimum coverage:

## Login Page Accessible

```text
GET /login
```

Expected:

```text
200 OK
```

---

## Login Success

Expected:

- authentication success
- redirect

---

## Login Failure

Expected:

- authentication error
- form displayed again

---

# Account Tests

Minimum coverage:

## Anonymous User

```text
GET /account
```

Expected:

```text
redirect to login
```

---

## Authenticated User

```text
GET /account
```

Expected:

```text
200 OK
```

---

# Component Tests

Reusable components should be tested when behavior exists.

Examples:

```text
Live Components
Twig Components
```

Simple presentation-only components do not necessarily require dedicated tests.

---

# Live Components

Live Components must be tested.

Verify:

- state updates
- actions
- validation

---

# Forms

Forms should validate:

- valid data
- invalid data
- required fields

---

# File Uploads

Upload features should verify:

- upload success
- validation errors
- invalid file handling

---

# Security

Security tests are mandatory.

Verify:

- anonymous access
- authenticated access
- role restrictions

---

# API Platform

Verify:

- CRUD availability
- response codes
- serialization

Do not introduce business API tests in the starter.

---

# Database Assertions

Avoid raw SQL assertions when possible.

Prefer:

```php
$userRepository->find(...)
```

or:

```php
self::assertCount(...)
```

---

# Mocking

Mock only external dependencies.

Examples:

- HTTP clients
- mail providers
- PDF services

Avoid mocking Doctrine entities.

Avoid mocking repositories without justification.

---

# Test Readability

Arrange:

```php
// Arrange
```

Act:

```php
// Act
```

Assert:

```php
// Assert
```

Pattern should be preferred.

---

# Test Independence

A test must never depend on:

- execution order
- another test
- previous state

Each test must be executable independently.

---

# CI Requirements

The following command must succeed:

```bash
make test
```

before merging any change.

---

# Completion Checklist

Before considering a feature complete:

- PHPUnit passes
- Functional tests pass
- Security tested
- Anonymous access tested
- Authenticated access tested
- No duplicated test setup
- AbstractWebTestCase reused

Testing quality is mandatory.

No feature is complete without tests.
