### Update a legacy project with modern framework

# PedalPal — Development Documentation

## Project Overview

Bike rental demo app. Flat-file PHP 8.5 backend + React 19 SPA frontend with Redis cache.

### Backend (PHP 8.5)

### Redis Cache Layer Implemented in Backend

### Data

- Beach cruisers: XML (`SampleData/beach_cruisers.xml`)
- Mountain bikes: JSON (`SampleData/mountain_bikes.json`, PascalCase keys)
- Accessories: JSON (`SampleData/accessories.json`)
- Electric bikes: JSON (`SampleData/electric_bikes.json`)

### Frontend (React 19 + TypeScript)

- **TanStack Router** — routes defined in `App.tsx` via `createRoute()`
- **TanStack Query** — hooks in `src/hooks/` with cache invalidation on mutations
- **Tailwind CSS v4** — `@import "tailwindcss"` in `index.css`
- **Components** — `src/components/ui/` (shadcn-style), `bikes/`, `accessories/`, `layout/`
- **API** — `src/lib/api.ts` (fetch wrapper, no axios)



## 1. Overview of Changes and Rationale

### 1.1 Backend Architecture

**PHPStan level 9 compliance**
All production PHP files pass PHPStan at the strictest level with zero errors, zero ignores. This was achieved by adding typed properties, return type declarations, array shape annotations (`@var list<array{...}>`), and fixing every implicit mixed-type assumption.

**Test suite addition**
The suite now has 82 tests across 10 test files, organized as:
- **Unit tests** (68) — each repository, service, cache adapter, and registry tested in isolation with temp files and mocked dependencies
- **Integration tests** (14) — end-to-end flows through the full handler/service/repository/cache chain using temp sample data

**Electric bike type**
Added the electric bike type end-to-end: `ElectricBikeRepository` (reads `electric_bikes.json`), registered in `ApplicationServices` with 6 default bikes (Volt Rider, City Glide, Trail Surge, Commute Ease, Hill Climber X, Breeze Electric), handler shortcut (`?action=electric`), frontend type, API function, TanStack Query hook, route (`/electric`), and `BikeCard` rendering (brand, range, motor power, weight, charge time). The `BikeServiceRegistry` pattern made this a single `$registry->register('electric', ...)` call — no code duplication.

**HTTP status code constants**
`src/HttpStatus.php` centralises all HTTP status codes (200, 400, 405, 500) used across `bike.php` and `accessory.php`. Removes 14 hard-coded integers, making the handlers self-documenting and easier to maintain.

**Route versioning (`/v1/` prefix)**
All API endpoints are now accessible under a versioned path:
- `/v1/handlers/bike?action=mountain`
- `backend/router.php` strips the prefix for PHP's built-in server (`php -S localhost:8080 router.php`)
- `backend/.htaccess` strips it for Apache (`RewriteRule ^v(\d+)/(.+)$ $2 [L,QSA]`)


**Build version display**
- `backend/VERSION` contains `1.0.0`
- `handlers/version.php` exposes `{version, api_version, name}` as JSON
- `hooks/useVersion.ts` fetches the version with `staleTime: Infinity` (React Query)
- `Footer.tsx` displays `v1 (1.0.0)` after successful fetch on every page
- Frontend `api.ts` changed `BASE_URL` from `''` to `'/v1'` — all API calls now use versioned URLs

**Cache layer improvements**
- `RedisCache::setMultiple()` uses `\Redis::PIPELINE` for atomic multi-set
- `NullCache` implements the `CacheInterface` contract explicitly (was implicit)
- `FileRepository` accepts `CacheInterface|null` via constructor injection — when null, falls back to `.json.cache` sidecar files; when a CacheInterface is provided (including NullCache), the file cache is skipped and only the adapter is used
- `Config::cache()` returns `null` (not NullCache) when Redis is unavailable, keeping the file-cache fallback active

**Bug fixes discovered through expanded testing**
- `array_is_list()` guard added to `MountainBikeRepository`, `ElectricBikeRepository`, and `AccessoryRepository` — JSON objects like `{"not_an_array": true}` previously passed the `is_array()` check and caused silent type confusion downstream
- Syntax fix in `ApplicationServices.php` — the mountain bike registration had `]);` instead of `]));` (missing closing parenthesis)
- Cache invalidation test on Windows uses `touch()` to ensure mtime changes (filemtime has 1-second resolution)

### 1.2 Frontend

**Test suite expansion (11 → 57 tests across 9 files)**
Added test files for every component and hook:

**Route versioning in the frontend**
- `BASE_URL` in `api.ts` changed to `'/v1'`, so all fetch calls go to `/v1/handlers/...`
- `vite.config.ts` proxies `/v1` to `http://localhost:8080` (Vite dev server rewrites the prefix away transparently for the PHP built-in server)


**General approach:**

1. **Understand the ask** — read the user's request, map it to concrete deliverables
2. **Explore the codebase** — find relevant files, understand existing patterns
3. **Design the solution** — decide on architecture, file locations, naming conventions
4. **Implement** — write code following existing conventions (strict types, PSR-12, Tailwind utilities)
5. **Test** — run the full suite (82 backend + 57 frontend tests)
6. **Verify** — run PHPStan level 9, TypeScript check, production build
7. **Iterate** — address user feedback, fix edge cases uncovered by tests

---

## 2. Key Assumptions, Trade-offs, and Limitations

### Assumptions

- **PHP 8.5+** — uses `array_is_list()`, `str_ends_with()`, `readonly` properties, typed class constants, and other PHP 8.x+ features. Will not run on PHP 7.x or earlier.
- **No framework** — the project intentionally avoids Laravel/Symfony/Slim. Routing is handled by the web server (nginx/Apache/PHP built-in server) directly mapping URLs to handler files. This was a constraint of the original legacy architecture.
- **Redis is optional** — when `REDIS_HOST` is unset, the app degrades gracefully to `.json.cache` sidecar files. No database is required.
- **Flat-file data sources** — beach cruisers live in XML, mountain/electric/accessories in JSON. There is no SQL database. This limits querying capability and data integrity guarantees.
- **Windows-compatible** — paths use `DIRECTORY_SEPARATOR`-safe constructs; test temp directories use `sys_get_temp_dir()`.

### Trade-offs

| Decision | Trade-off |
|----------|-----------|
| **Registry pattern over hardcoded services** | Slightly more complex at setup time, but adding a new bike type is one `$registry->register()` call instead of copy-pasting an entire service class |
| **Constructor injection over setters** | Cleaner object lifecycle, but required refactoring all repository instantiations at once |
| **Direct `require` in router.php** | No framework overhead, but PHP built-in server must run with the router flag; plain `php -S` without it won't handle `/v1/` URLs |
| **Frontend keeps discriminated union in BikeCard** | Simple and explicit, but if bike types grow beyond 3-4, a generic renderer would scale better |
| **File cache fallback over hard Redis dependency** | Works without any infrastructure, but stale cache files can linger on disk |


### Limitations

- **No authentication/authorization** — any client can rent bikes or reset data. This is a demo app.
- **No request validation middleware** — each handler validates its own input inline. In a larger system, a validation layer would be extracted.
- **PHP built-in server is single-threaded** — `php -S` handles one request at a time. For any real traffic, use Apache/nginx + PHP-FPM or the Docker setup.
- **Flat-file concurrency** — multiple simultaneous write requests could corrupt JSON/XML files. The app is read-heavy (bike listings) with rare writes (rent, reset), but this is not safe for concurrent write workloads.
- **No database migrations** — data schema changes require manual updates to both sample data files and repository `loadFromSource()` methods.
- **Route versioning** 

---

## 3. Integration into a Larger System

### API Gateway / Reverse Proxy

In a production deployment, the nginx instance could sit behind an API gateway (AWS API Gateway). The `/v1/` URL prefix allows the gateway to route based on version without inspecting request bodies. A future `/v2/` can coexist alongside `/v1/` during migration.

### Microservice Extraction

Each handler file (`bike.php`, `accessory.php`, `version.php`) is a self-contained HTTP endpoint. In a microservice architecture:
- `bike.php` → **Bike Service** (owns bike data, rental state)
- `accessory.php` → **Accessory Service** (owns inventory, order processing)
- `version.php` → **Health/Version endpoint** (deployment metadata)

The `FileRepository` → `CacheInterface` abstraction means each service could swap flat files for a PostgreSQL/MySQL repository without changing the service layer.

### CI/CD Pipeline

GitHub Actions workflow can be added which will validate code quality, errors and tests at every commit:
1. PHP CS Fixer (style)
2. PHPStan level 9 (static analysis)
3. PHPUnit (unit + integration tests)
4. TypeScript check
5. ESLint
6. Vitest
7. Vite production build
8. Docker image build (cached layers)

A CD step could push the Docker image to a registry and deploy to Kubernetes or a VPS.

### Observability

- Redis cache keys follow the pattern `pedalpal:data:<path>` — easy to inspect with `redis-cli --scan`
- use other tools for observability
- logging
---

## 4. Full Application Overview, Structure, and How to Run

### Application Overview

PedalPal is a **bike rental demo application** with three bike types (beach cruiser, mountain, electric) and an accessory shop. Users browse bikes, rent them, and purchase compatible accessories. The backend is a flat-file PHP 8.5 application with a Redis cache layer. The frontend is a React 19 single-page application using TanStack Router for client-side routing and TanStack Query for server state management.

### Complete Directory Structure

```
root
│
├── DOCUMENTATION.md               # This file│
├── backend/                       # PHP 8.5 API
│   ├── .env.example               # Environment template
│   ├── .htaccess                  # Apache rewrite for /v1/ prefix
│   ├── VERSION                    # Current build version
│   ├── composer.json              # Dependencies (phpdotenv, dev: phpstan/phpunit)
│   ├── phpstan.neon               # PHPStan level 9 config
│   ├── phpunit.xml                # PHPUnit config
│   ├── router.php                 # PHP built-in server router (strips /v1/)
│   │
│   ├── data/                      # Repository implementations (global ns)
│   │   ├── BeachCruiserRepository.php   # XML reader
│   │   ├── MountainBikeRepository.php   # JSON reader (PascalCase)
│   │   ├── ElectricBikeRepository.php   # JSON reader
│   │   └── AccessoryRepository.php      # JSON reader
│   │
│   │   ├── handlers/                  # HTTP endpoint files (clean URLs)
│   │   │   ├── bike.php              # /v1/handlers/bike?action=beach|mountain|electric|rent|reset
│   │   │   ├── accessory.php         # /v1/handlers/accessory (GET list / POST order)
│   │   │   └── version.php           # /v1/handlers/version (GET → {version, api_version, name})
│   │
│   ├── services/                  # Business logic (global ns)
│   │   ├── AccessoryService.php        # Compatibility, pricing, bundle discounts
│   │   └── ApplicationServices.php     # Composition root / service locator
│   │
│   ├── src/                       # PSR-4 namespace: PedalPal\
│   │   ├── autoload.php           # Simple PSR-4 autoloader
│   │   ├── Config.php             # Redis connection from env vars
│   │   ├── HttpStatus.php         # HTTP status code constants
│   │   ├── Cache/
│   │   │   ├── CacheInterface.php
│   │   │   ├── NullCache.php
│   │   │   └── RedisCache.php
│   │   ├── Repository/
│   │   │   └── FileRepository.php # Abstract base (load/source/cache)
│   │   └── Service/
│   │       ├── BikeService.php
│   │       ├── BikeServiceInterface.php
│   │       └── BikeServiceRegistry.php
│   │
│   ├── SampleData/               # Flat-file data sources
│   │   ├── beach_cruisers.xml
│   │   ├── mountain_bikes.json
│   │   ├── electric_bikes.json
│   │   └── accessories.json
│   │
│   └── tests/
│       ├── bootstrap.php
│       ├── Integration/
│       │   └── BikeHandlerTest.php      
│       └── Unit/
│           ├── AccessoryRepositoryTest.php
│           ├── AccessoryServiceTest.php
│           ├── BeachCruiserRepositoryTest.php
│           ├── BikeServiceRegistryTest.php
│           ├── BikeServiceTest.php
│           ├── CacheTest.php
│           ├── ElectricBikeRepositoryTest.php
│           ├── FileRepositoryTest.php
│           ├── MountainBikeRepositoryTest.php
│           └── VersionHandlerTest.php
│
├── frontend/                     # React 19 SPA
    ├── .env.example
    ├── index.html
    ├── package.json
    ├── tsconfig.json / tsconfig.app.json / tsconfig.node.json
    ├── vite.config.ts
    ├── vitest.config.ts
    ├── eslint.config.js
    │
    ├── public/
    │   ├── favicon.svg
    │   └── icons.svg
    │
    └── src/
        ├── main.tsx
        ├── App.tsx                # Router + QueryClient setup
        ├── index.css              # Tailwind v4 import
        │
        ├── components/
        │   ├── accessories/
        │   │   ├── AccessoryItem.tsx
        │   │   ├── AccessoryModal.tsx
        │   │   └── BundleBanner.tsx
        │   ├── bikes/
        │   │   ├── BikeCard.tsx          
        |   |   ├── BikeCardLayout.tsx    
        |   |   ├── DetailRow.tsx         
        |   |   ├── BeachCruiserCard.tsx  
        |   |   ├── MountainBikeCard.tsx   
        |   |   |── ElectricBikeCard.tsx
        |   |   └── BikeGrid.tsx
        │   ├── layout/
        │   │   ├── Footer.tsx     # Displays version from backend
        │   │   └── Header.tsx
        │   └── ui/               # shadcn/ui-style primitives
        │       ├── badge.tsx
        │       ├── button.tsx
        │       ├── card.tsx
        │       ├── dialog.tsx
        │       └── toast.tsx
        │
        ├── hooks/
        │   ├── useAccessories.ts
        │   ├── useBikes.ts
        │   └── useVersion.ts
        │
        ├── lib/
        │   ├── api.ts            # BASE_URL = '/v1', all API functions
        │   └── utils.ts
        │
        ├── pages/
        │   ├── HomePage.tsx      # Category cards + reset
        │   └── BikeListPage.tsx  # Bike grid + accessory modal
        │
        ├── test/                 # 57 tests across 9 files + setup
        │   ├── setup.ts
        │   ├── AccessoryItem.test.tsx
        │   ├── api.test.ts
        │   ├── Badge.test.tsx
        │   ├── BikeCard.test.tsx
        │   ├── BikeGrid.test.tsx
        │   ├── Button.test.tsx
        │   ├── Footer.test.tsx
        │   ├── useBikes.test.tsx
        │   └── useVersion.test.tsx
        │
        └── types/
            ├── accessory.ts
            └── bike.ts

```

## Coding Conventions

- PHP: `declare(strict_types=1)` always, typed properties, PSR-12, phpstan
- React: Arrow function components, `memo` for render optimization, custom hooks
- CSS: Tailwind utility classes only (no custom CSS files)
- Tests: PHPUnit for PHP, Vitest + RTL for React

### How to Run

Prerequisites: PHP 8.5+, Node 22+, Redis (optional, for cache).

Terminal 1 — PHP API:
```bash
cd backend
php -S localhost:8080 router.php
# API:   http://localhost:8080/v1/handlers/bike?action=mountain
```

Terminal 2 — Frontend:
```bash
cd frontend
npm install
npm run dev
# Frontend: http://localhost:5173
```

### Running Tests

```bash
# Backend (PHPUnit — 82 tests)
cd backend
composer test
# or: php -d memory_limit=512M vendor/bin/phpunit

# Frontend (Vitest — 57 tests)
cd frontend
npx vitest run

# Build frontend
cd frontend
npm run build

# Static analysis
cd backend
composer analyse
```

### API Endpoints

All endpoints are under the `/v1/` prefix. Unversioned paths (`/handlers/...`) are maintained for backward compatibility.

| Method | Path | Description |
|--------|------|-------------|
| GET | `/v1/handlers/bike?action=beach` | List beach cruisers |
| GET | `/v1/handlers/bike?action=mountain` | List mountain bikes |
| GET | `/v1/handlers/bike?action=electric` | List electric bikes |
| GET | `/v1/handlers/bike?action=list&type=beach\|mountain\|electric` | Generic type lookup |
| POST | `/v1/handlers/bike?action=rent` | Rent a bike `{bikeType, bikeId}` |
| POST | `/v1/handlers/bike?action=reset` | Reset all data to defaults |
| GET | `/v1/handlers/accessory?bikeType=beach\|mountain\|electric` | List compatible accessories |
| POST | `/v1/handlers/accessory` | Submit accessory order |
| GET | `/v1/handlers/version` | Build version info |

### Screenshots

<img src="Modern%20approach/images/1.jpeg" width="500">
<img src="Modern%20approach/images/4.jpeg" width="500">
<img src="Modern%20approach/images/2.jpeg" width="500">
<img src="Modern%20approach/images/3.jpeg" width="500">
<img src="Modern%20approach/images/5.jpeg" width="500">
<img src="Modern%20approach/images/6.jpeg" width="500">
