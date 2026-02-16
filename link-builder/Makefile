.PHONY: help build up down restart logs shell db-shell migrate seed fresh queue node install setup

# Default target
help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

# ============================================================================
# Docker Commands
# ============================================================================

build: ## Build Docker images
	docker compose build

up: ## Start all containers
	cp -n .env.docker .env 2>/dev/null || true
	docker compose up -d

up-dev: ## Start all containers with Vite dev server
	cp -n .env.docker .env 2>/dev/null || true
	docker compose --profile dev up -d

down: ## Stop all containers
	docker compose down

restart: ## Restart all containers
	docker compose restart

destroy: ## Stop and remove all containers, volumes, and images
	docker compose down -v --rmi local

logs: ## View logs from all containers
	docker compose logs -f

logs-app: ## View app container logs
	docker compose logs -f app

# ============================================================================
# Shell Access
# ============================================================================

shell: ## Open a shell in the app container
	docker compose exec app sh

db-shell: ## Open MySQL shell
	docker compose exec db mysql -u linkbuilder -psecret linkbuilder

redis-cli: ## Open Redis CLI
	docker compose exec redis redis-cli

# ============================================================================
# Laravel Commands
# ============================================================================

migrate: ## Run database migrations
	docker compose exec app php artisan migrate

seed: ## Run database seeders
	docker compose exec app php artisan db:seed

fresh: ## Fresh migration with seeders
	docker compose exec app php artisan migrate:fresh --seed

tinker: ## Open Laravel Tinker
	docker compose exec app php artisan tinker

cache-clear: ## Clear all Laravel caches
	docker compose exec app php artisan cache:clear
	docker compose exec app php artisan config:clear
	docker compose exec app php artisan route:clear
	docker compose exec app php artisan view:clear

optimize: ## Optimize for production
	docker compose exec app php artisan optimize

# ============================================================================
# Queue
# ============================================================================

queue: ## Start queue worker
	docker compose exec app php artisan queue:work

# ============================================================================
# Node / Frontend
# ============================================================================

node: ## Enter the node container
	docker compose --profile dev exec node sh

npm-build: ## Build frontend assets
	docker compose --profile dev exec node npm run build

# ============================================================================
# Setup
# ============================================================================

install: ## First time setup
	@echo "🚀 Setting up Link Builder..."
	cp -n .env.docker .env 2>/dev/null || true
	docker compose build
	docker compose up -d
	@echo "⏳ Waiting for database..."
	sleep 15
	docker compose exec app php artisan key:generate --force
	docker compose exec app php artisan migrate --force
	docker compose exec app php artisan storage:link
	@echo "✅ Link Builder is ready at http://localhost:8080"

setup: install ## Alias for install
