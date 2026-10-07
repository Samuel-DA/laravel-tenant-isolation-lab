# Laravel Tenant Isolation Lab

A practical Laravel 13 demonstration of defence-in-depth tenant isolation for shared-database SaaS applications.

The project answers one question:

> How do we make it difficult for Tenant A to ever retrieve, modify, cache, process, or download Tenant B’s private data?

## What This Demonstrates

Tenant isolation is enforced across multiple boundaries:

- Central tenant context
- Fail-closed behaviour when context is missing
- Request-level tenant identification
- Tenant-scoped Eloquent queries
- Independent authorization policies
- Tenant-aware project listing, viewing, updating, and deletion
- Private tenant-specific document storage
- Database-enforced tenant ownership
- Tenant-namespaced cache entries
- Explicit tenant context in queued jobs
- Context cleanup after requests and background jobs
- Adversarial cross-tenant tests

## Architecture

```text
Incoming request
       |
       v
Identify tenant from X-Tenant header
       |
       v
Authenticate user
       |
       v
Verify user belongs to active tenant
       |
       v
Apply tenant-scoped queries
       |
       v
Authorize requested operation
       |
       v
Database, cache and storage boundaries
```

Tenant context is cleared after every request and queued job so that long-running processes cannot accidentally reuse stale tenant state.

## Demonstration Tenants

The examples use two fictional organisations:

- Acme Ltd
- Globex Ltd

Tests deliberately attempt to cross the boundary between them.

## Isolation Test Matrix

| Operation | Same tenant | Different tenant |
|---|---:|---:|
| List projects | Allowed | Hidden |
| View project | Allowed | `404` |
| Update project | Allowed | `404` |
| Delete project | Allowed | `404` |
| Download document | Allowed | `404` |
| Read cached statistics | Isolated | Isolated |
| Generate queued report | Allowed | Rejected |
| Query without context | Rejected | Rejected |
| Cross-tenant document relationship | Allowed when valid | Rejected by database |

## Technology

- PHP 8.3+
- Laravel 13
- SQLite for the demonstration environment
- Laravel feature and unit tests

No third-party multi-tenancy package is used. The project exposes the underlying architectural controls so they can be studied independently.

## Installation

Clone the repository and install its dependencies:

```bash
composer install
```

Create the local environment file:

```bash
cp .env.example .env
```

Generate an application key:

```bash
php artisan key:generate
```

Create the SQLite database:

```bash
touch database/database.sqlite
```

Set the database connection in `.env`:

```env
DB_CONNECTION=sqlite
```

Run the migrations:

```bash
php artisan migrate
```

Run the test suite:

```bash
php artisan test
```

## Verified Test Result

```text
Tests: 30 passed (53 assertions)
```

## Demonstration Request

Tenant context is supplied through the `X-Tenant` header:

```http
GET /demo/projects
X-Tenant: acme
```

The header is deliberately simple for demonstration purposes. A production application might resolve tenants through verified subdomains, custom domains, API credentials, or an authenticated workspace selection.

Tenant identification is not treated as authorization. The authenticated user must still belong to the resolved tenant.

## Important Security Notes

This repository is an educational reference implementation, not a drop-in production tenancy package.

Production systems should additionally consider:

- Rate limiting
- Stronger identity and membership models
- Audit logging
- Search-index isolation
- Export and import boundaries
- Webhook authentication
- Object-storage permissions
- Platform administrator controls
- Monitoring and incident response
- Database-specific row-level security where appropriate

## Why This Project Exists

Multi-tenancy is easy to implement badly because every individual query can appear valid while operating outside the correct customer boundary.

The goal is not to rely on developers remembering to add `WHERE tenant_id = ?` everywhere. The goal is to combine multiple independent controls so that one mistake does not immediately become a cross-tenant data leak.

## Author

Samuel Dolapo Adebiyi is a backend software engineer and the founder of AGSSOFT. He builds SaaS and distributed backend systems using PHP, Laravel, Go, SQL, cloud infrastructure, and event-driven technologies. He is currently building CentiHR, a multi-tenant HR and compliance platform.