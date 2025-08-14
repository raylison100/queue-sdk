#!/bin/bash

# Queue SDK Load Test Dashboard Launcher
# ======================================

cd "$(dirname "$0")"

DASHBOARD_PORT=${1:-8080}
DASHBOARD_HOST="localhost"

echo "🎯 Queue SDK Load Test Dashboard"
echo "================================"
echo ""
echo "🚀 Iniciando servidor do dashboard..."
echo "📊 URL: http://${DASHBOARD_HOST}:${DASHBOARD_PORT}"
echo "📁 Diretório: $(pwd)"
echo ""
echo "🔄 Gerando dados iniciais..."

# Gerar dados iniciais do dashboard
php server.php generate

echo "✅ Dados gerados!"
echo ""
echo "🌐 Iniciando servidor HTTP na porta ${DASHBOARD_PORT}..."
echo ""
echo "📖 Como usar:"
echo "   • Abra: http://${DASHBOARD_HOST}:${DASHBOARD_PORT}"
echo "   • API: http://${DASHBOARD_HOST}:${DASHBOARD_PORT}/api/test-results"
echo "   • Pressione Ctrl+C para parar"
echo ""

# Iniciar servidor PHP
php -S ${DASHBOARD_HOST}:${DASHBOARD_PORT} server.php
