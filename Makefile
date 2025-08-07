# Makefile para Queue SDK

.DEFAULT_GOAL := help
.PHONY: help dev shell test example clean

# Cores
GREEN := \033[0;32m
BLUE := \033[0;34m
YELLOW := \033[1;33m
NC := \033[0m

help:
	@echo ""
	@echo "$(BLUE)Queue SDK - Comandos Disponíveis$(NC)"
	@echo ""
	@echo "$(YELLOW)Desenvolvimento:$(NC)"
	@echo "  make dev            - Iniciar ambiente de desenvolvimento"
	@echo "  make shell          - Abrir shell no container"
	@echo ""
	@echo "$(YELLOW)Testes:$(NC)"
	@echo "  make test           - Executar testes"
	@echo ""
	@echo "$(YELLOW)Exemplos:$(NC)"
	@echo "  make example        - Executar exemplo básico"
	@echo ""
	@echo "$(YELLOW)Limpeza:$(NC)"
	@echo "  make clean          - Parar containers e limpar"
	@echo ""

dev:
	@echo "$(GREEN)🚀 Iniciando ambiente de desenvolvimento...$(NC)"
	docker-compose up -d
	@echo "$(GREEN)✅ Ambiente iniciado! Use 'make shell' para acessar$(NC)"

shell:
	@echo "$(GREEN)🐚 Abrindo shell no container...$(NC)"
	docker-compose exec queue-sdk-dev bash

test:
	@echo "$(GREEN)🧪 Executando testes...$(NC)"
	docker-compose exec queue-sdk-dev vendor/bin/phpunit --no-coverage

example:
	@echo "$(GREEN)📋 Executando exemplo básico...$(NC)"
	docker-compose exec queue-sdk-dev php examples/basic-example.php

clean:
	@echo "$(GREEN)🧹 Limpando ambiente...$(NC)"
	docker-compose down --volumes
	@echo "$(GREEN)✅ Limpeza concluída!$(NC)"
