# Makefile para Queue SDK

.DEFAULT_GOAL := help
.PHONY: help up down shell test logs status clean dashboard stop-dashboard clean-tests clean-all demo setup-topics

# Cores
GREEN := \033[0;32m
BLUE := \033[0;34m
YELLOW := \033[1;33m
RED := \033[0;31m
NC := \033[0m

help:
	@echo ""
	@echo "$(BLUE)Queue SDK - Comandos Disponíveis$(NC)"
	@echo ""
	@echo "$(YELLOW)🐳 Ambiente:$(NC)"
	@echo "  make up               - Subir ambiente completo (PHP + Kafka + SQS + Dashboard)"
	@echo "  make down             - Derrubar ambiente completo"
	@echo "  make shell            - Abrir shell no container PHP"
	@echo ""
	@echo "$(YELLOW)🧪 Testes:$(NC)"
	@echo "  make test             - Executar testes unitários no container"
	@echo "  make clean-tests      - Limpar arquivos de teste (JSON, logs)"
	@echo ""
	@echo "$(YELLOW)📊 Dashboard:$(NC)"
	@echo "  make dashboard        - Iniciar apenas dashboard de performance"
	@echo "  make stop-dashboard   - Parar dashboard de performance"
	@echo ""
	@echo "$(YELLOW)🔧 Serviços:$(NC)"
	@echo "  make logs             - Ver logs de todos os serviços"
	@echo "  make status           - Status dos containers"
	@echo ""
	@echo "$(YELLOW)🌐 Interfaces Web:$(NC)"
	@echo "  Dashboard:     http://localhost:8080"
	@echo "  Kafka UI:      http://localhost:8083"
	@echo "  SQS Web:       http://localhost:9325"
	@echo ""
	@echo "$(YELLOW)🚀 Scripts:$(NC)"
	@echo "  make demo             - Demo interativa completa"
	@echo "  make setup-topics     - Configurar tópicos Kafka otimizados"
	@echo ""
	@echo "$(YELLOW)🧹 Limpeza:$(NC)"
	@echo "  make clean            - Parar containers e limpar volumes"
	@echo "  make clean-all        - Limpeza completa (containers + tests + cache)"
	@echo ""

up:
	@echo "$(GREEN)🚀 Subindo ambiente completo...$(NC)"
	docker-compose up -d
	@cd example-project/tests/load && ./start.sh &
	@sleep 3
	@echo "$(GREEN)✅ Ambiente iniciado! Dashboard: http://localhost:8080$(NC)"

down:
	@echo "$(RED)🛑 Derrubando ambiente...$(NC)"
	-@pkill -f "php.*server.php"
	docker-compose down --volumes --remove-orphans
	@echo "$(GREEN)✅ Ambiente derrubado!$(NC)"

shell:
	docker-compose exec queue-sdk-dev bash

test:
	docker-compose exec queue-sdk-dev vendor/bin/phpunit --no-coverage

logs:
	docker-compose logs --tail=50 -f

status:
	docker-compose ps

clean:
	docker-compose down --volumes --remove-orphans

demo:
	@chmod +x scripts/demo.sh
	./scripts/demo.sh

setup-topics:
	@chmod +x scripts/setup-kafka-topics.sh
	./scripts/setup-kafka-topics.sh

clean-tests:
	@rm -f example-project/tests/load/*.json || true
	@rm -f example-project/tests/load/data/progress/*.json || true
	@rm -f example-project/tests/load/data/results/*.json || true
	@rm -f *.log || true
	@rm -f /tmp/dashboard-server.log || true

clean-all: clean clean-tests
	@docker system prune -f --volumes 2>/dev/null || true

dashboard:
	@docker-compose up -d
	@chmod +x example-project/tests/load/start.sh
	cd example-project/tests/load && ./start.sh

stop-dashboard:
	@pkill -f "php.*server.php" || true
