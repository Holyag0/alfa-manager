.PHONY: help build up down restart logs deploy backup restore monitor clean artisan

# Variáveis
COMPOSE_FILE = docker-compose.prod.yml
COMPOSE = docker-compose -f $(COMPOSE_FILE)

# Cores
GREEN = \033[0;32m
YELLOW = \033[1;33m
NC = \033[0m

help: ## Mostra esta ajuda
	@echo "$(GREEN)Comandos disponíveis:$(NC)"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  $(YELLOW)%-15s$(NC) %s\n", $$1, $$2}'

build: ## Construir imagens Docker
	@echo "$(YELLOW)🏗️  Construindo imagens...$(NC)"
	$(COMPOSE) build --no-cache

up: ## Iniciar containers
	@echo "$(YELLOW)🚀 Iniciando containers...$(NC)"
	$(COMPOSE) up -d
	@echo "$(GREEN)✅ Containers iniciados!$(NC)"

down: ## Parar containers
	@echo "$(YELLOW)🛑 Parando containers...$(NC)"
	$(COMPOSE) down
	@echo "$(GREEN)✅ Containers parados!$(NC)"

restart: ## Reiniciar containers
	@echo "$(YELLOW)🔄 Reiniciando containers...$(NC)"
	$(COMPOSE) restart
	@echo "$(GREEN)✅ Containers reiniciados!$(NC)"

logs: ## Ver logs de todos os containers
	$(COMPOSE) logs -f

logs-app: ## Ver logs do container app
	$(COMPOSE) logs -f app

logs-nginx: ## Ver logs do nginx
	$(COMPOSE) logs -f nginx

logs-queue: ## Ver logs da queue
	$(COMPOSE) logs -f queue

status: ## Status dos containers
	$(COMPOSE) ps

deploy: ## Deploy completo
	@./deploy.sh

backup: ## Criar backup do banco
	@./backup.sh

restore: ## Restaurar backup (uso: make restore FILE=backup.sql.gz)
	@./restore.sh $(FILE)

monitor: ## Monitorar containers
	@./monitor.sh

clean: ## Limpar containers, volumes e imagens
	@echo "$(YELLOW)⚠️  ATENÇÃO: Isso irá remover TODOS os containers, volumes e imagens!$(NC)"
	@read -p "Deseja continuar? (yes/no): " confirm && [ "$$confirm" = "yes" ] || exit 1
	$(COMPOSE) down -v
	docker system prune -a -f
	@echo "$(GREEN)✅ Limpeza concluída!$(NC)"

artisan: ## Executar comando artisan (uso: make artisan CMD="migrate")
	$(COMPOSE) exec app php artisan $(CMD)

shell: ## Entrar no container app
	$(COMPOSE) exec app bash

mysql: ## Entrar no MySQL (carrega variáveis do .env)
	@if [ -f .env.production ]; then \
		export $$(cat .env.production | grep -v '#' | grep -v '^$$' | xargs); \
		$(COMPOSE) exec mysql mysql -u$${DB_USERNAME} -p$${DB_PASSWORD} $${DB_DATABASE}; \
	else \
		echo "$(YELLOW)⚠️  Arquivo .env.production não encontrado$(NC)"; \
		exit 1; \
	fi

redis-cli: ## Entrar no Redis CLI
	$(COMPOSE) exec redis redis-cli

composer: ## Executar composer (uso: make composer CMD="install")
	$(COMPOSE) exec app composer $(CMD)

npm: ## Executar npm (uso: make npm CMD="install")
	$(COMPOSE) exec app npm $(CMD)

migrate: ## Executar migrations
	$(COMPOSE) exec app php artisan migrate

migrate-fresh: ## Recriar banco (CUIDADO!)
	$(COMPOSE) exec app php artisan migrate:fresh --seed

seed: ## Executar seeders
	$(COMPOSE) exec app php artisan db:seed

cache-clear: ## Limpar todos os caches
	$(COMPOSE) exec app php artisan cache:clear
	$(COMPOSE) exec app php artisan config:clear
	$(COMPOSE) exec app php artisan route:clear
	$(COMPOSE) exec app php artisan view:clear

optimize: ## Otimizar aplicação
	$(COMPOSE) exec app php artisan config:cache
	$(COMPOSE) exec app php artisan route:cache
	$(COMPOSE) exec app php artisan view:cache
	$(COMPOSE) exec app php artisan event:cache

queue-restart: ## Reiniciar queue workers
	$(COMPOSE) restart queue
	$(COMPOSE) exec app php artisan queue:restart

test: ## Executar testes
	$(COMPOSE) exec app php artisan test

permissions: ## Corrigir permissões
	$(COMPOSE) exec app chmod -R 775 storage bootstrap/cache
	$(COMPOSE) exec app chown -R sail:sail storage bootstrap/cache