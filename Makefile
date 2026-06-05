COMPOSE=docker compose --env-file .env.docker -f docker-compose.local.yml

up:
	$(COMPOSE) up -d

up-build:
	$(COMPOSE) up -d --build

build-no-cache:
	$(COMPOSE) build --no-cache

down:
	$(COMPOSE) down

restart:
	$(COMPOSE) down
	$(COMPOSE) up -d

rebuild:
	$(COMPOSE) down
	$(COMPOSE) up -d --build

ps:
	$(COMPOSE) ps

ps-a:
	$(COMPOSE) ps -a

logs:
	$(COMPOSE) logs -f

logs-php:
	$(COMPOSE) logs -f php

php:
	$(COMPOSE) exec php sh

migrate:
	$(COMPOSE) exec php php artisan migrate

composer-install:
	$(COMPOSE) exec php composer install

composer-update:
	$(COMPOSE) exec php composer update

artisan:
	$(COMPOSE) exec php php artisan $(cmd)

node:
	$(COMPOSE) exec node sh

npm-install:
	$(COMPOSE) exec node npm install

npm-dev:
	$(COMPOSE) exec node npm run dev -- --host 0.0.0.0 --port 5173

npm-build:
	$(COMPOSE) exec node npm run build

logs-node:
	$(COMPOSE) logs -f node
