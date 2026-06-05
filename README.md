# Laravel Backend Docker Setup

Local Docker infrastructure for a Laravel backend project.

## Start

1. Copy the Docker environment file:

```sh
cp .env.docker.example .env.docker
```

2. Start containers:

```sh
make up
```

3. Install Laravel manually inside the `backend` folder.

4. Install Composer dependencies:

```sh
make composer-install
```

5. Configure `backend/.env` for PostgreSQL:

```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret
```

6. Run migrations:

```sh
make migrate
```

## Useful Commands

```sh
make up
make down
make ps
make ps-a
make logs
make logs-php
make php
make composer-install
make composer-update
make migrate
make artisan cmd="route:list"
make artisan cmd="migrate:fresh --seed"
```
