# App Icon Fetcher

App Icon Fetcher is a small Laravel-based MVP service that helps users quickly fetch app icon URLs from Apple App Store and Google Play.

The user can paste a Google Play URL, Apple App Store URL, bundle/package ID, or Apple App ID.
The service detects the input type, fetches available app icons, and shows a clear result for each store.

## Supported Input Formats

### Google Play URL

```text
https://play.google.com/store/apps/details?id=com.u1.relax.minigame3&hl=uk
```

### Apple App Store URL

```text
https://apps.apple.com/ua/app/all-in-hole-black-hole-games/id6503284107?l=ru
```

### Bundle / Package ID

```text
com.block.juggle
```

### Apple App ID

```text
6503284107
```

or:

```text
id6503284107
```

## Tech Stack

- Laravel 13
- Inertia
- Vue 3
- TypeScript
- Docker Compose
- PostgreSQL
- PHPUnit

## Quick Start

Copy the Docker environment file:

```sh
cp .env.docker.example .env.docker
```

Start containers:

```sh
docker compose -f docker-compose.local.yml --env-file .env.docker up -d
```

Install Composer dependencies:

```sh
docker compose -f docker-compose.local.yml --env-file .env.docker exec -T php composer install
```

Install frontend dependencies:

```sh
docker compose -f docker-compose.local.yml --env-file .env.docker exec -T node npm install
```

Copy Laravel environment file:

```sh
cp backend/.env.example backend/.env
```

Generate application key:

```sh
docker compose -f docker-compose.local.yml --env-file .env.docker exec -T php php artisan key:generate
```

Run migrations:

```sh
docker compose -f docker-compose.local.yml --env-file .env.docker exec -T php php artisan migrate
```

Build frontend assets:

```sh
docker compose -f docker-compose.local.yml --env-file .env.docker exec -T node npm run build
```

## Usage

Open the app in the browser:

```text
http://localhost:8080
```

Create an account or log in.

After login, the user is redirected to:

```text
http://localhost:8080/app-icon-fetcher
```

Paste one of the supported inputs and click:

```text
Fetch Icons
```

The page will show two result cards:

- Apple App Store
- Google Play

Each card shows either:

- the app icon image and icon URL;
- or a clear message if the icon could not be found.

## Time Spent

Approximately: `3 hours`

## AI Usage

codex
