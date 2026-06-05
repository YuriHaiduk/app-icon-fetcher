# Project Instructions

This repository contains a Docker-based Laravel 13 test task project.

The goal of the project is to build a product-ready MVP for the **App Icon Fetcher** service.

The service must allow users to provide one of the following inputs:

- Google Play URL  
  Example: `https://play.google.com/store/apps/details?id=com.u1.relax.minigame3&hl=uk`

- Apple App Store URL  
  Example: `https://apps.apple.com/ua/app/pubg-mobile/id1330123889?l=ru`

- Plain bundle/package id  
  Example: `com.u1.relax.minigame3`

The application should normalize the input, detect its type, fetch icon URLs from supported app stores, and return a stable response without crashing if one store does not contain the app.

The main Laravel application is located in the `backend` directory.

Before changing application code, read and follow:

`backend/AGENTS.md`

## Important

Do not use the Makefile.

The Makefile is intended only for the project owner.

Use Docker Compose commands directly.

Run all commands from the repository root.

## Project structure

- `backend/` — Laravel 13 application with Inertia, Vue 3, TypeScript, Auth starter kit, tests, and Laravel Boost.
- `docker/` — Docker configuration.
- `docker-compose.local.yml` — local Docker Compose configuration.
- `.env.docker` — Docker environment variables.
- `.codex/config.toml` — Codex MCP configuration.
- `Makefile` — owner-only helper commands. Do not use it.

## Application architecture

Implement the App Icon Fetcher as a self-contained Laravel module inside:

`backend/modules/AppIconFetcher`

The module should contain everything related to this feature:

- HTTP controllers
- Form requests
- API resources
- Application services
- DTOs
- Enums
- Store providers
- Contracts
- Exceptions
- Tests related to the feature
- Frontend page if needed

Keep the module cohesive and isolated from unrelated application code.

Follow SOLID principles and Laravel best practices.

Prefer clear, maintainable code over overengineering.

## Expected high-level flow

The feature should follow this flow:

```text
User input
    ↓
Input resolver / normalizer
    ↓
Normalized app input
    ↓
Apple App Store provider
Google Play provider
    ↓
Store icon results
    ↓
API response / UI response