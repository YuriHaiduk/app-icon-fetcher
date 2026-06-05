# Project Instructions

This repository contains a Docker-based Laravel test task.

The main application is located in the `backend` directory.

Before changing application code, read and follow:

`backend/AGENTS.md`

## Important

Do not use the Makefile.

The Makefile is intended only for the project owner.

Use Docker Compose commands directly.

## Project structure

- `backend/` — Laravel 13 application with Inertia, Vue, TypeScript, Auth starter kit, and Laravel Boost.
- `docker/` — Docker configuration.
- `docker-compose.local.yml` — local Docker Compose configuration.
- `.env.docker` — Docker environment variables.
- `.codex/config.toml` — Codex MCP configuration.
- `Makefile` — owner-only helper commands. Do not use it.

## Working with Laravel

The Laravel application is inside the `backend` directory, but commands should be executed through the `php` Docker service.

Run commands from the repository root.

Examples:

```bash
docker compose -f docker-compose.local.yml --env-file .env.docker exec -T php php artisan