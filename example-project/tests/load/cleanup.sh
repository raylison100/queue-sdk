#!/bin/bash

# Queue SDK - Load Test Data Cleanup
# ==================================

cd "$(dirname "$0")"

DAYS=${1:-7}  # Manter arquivos dos últimos 7 dias por padrão

echo "🧹 Queue SDK - Limpeza de Dados de Teste"
echo "========================================"
echo ""
echo "📁 Limpando arquivos com mais de $DAYS dias..."

# Limpar arquivos de progresso antigos
if [ -d "data/progress" ]; then
    PROGRESS_COUNT=$(find data/progress -name "progress-*.json" -mtime +$DAYS | wc -l)
    if [ $PROGRESS_COUNT -gt 0 ]; then
        echo "🗑️  Removendo $PROGRESS_COUNT arquivos de progresso antigos..."
        find data/progress -name "progress-*.json" -mtime +$DAYS -delete
    else
        echo "✅ Nenhum arquivo de progresso antigo encontrado"
    fi
fi

# Limpar arquivos de resultado antigos (manter pelo menos os 10 mais recentes)
if [ -d "data/results" ]; then
    TOTAL_RESULTS=$(ls -1 data/results/result-*.json 2>/dev/null | wc -l)
    if [ $TOTAL_RESULTS -gt 10 ]; then
        OLD_RESULTS=$(ls -1t data/results/result-*.json | tail -n +11 | xargs ls -la | awk '$6 " " $7 " " $8 < "'$(date -d "$DAYS days ago" '+%b %d %Y')'"' | wc -l)
        if [ $OLD_RESULTS -gt 0 ]; then
            echo "🗑️  Removendo $OLD_RESULTS arquivos de resultado antigos (mantendo os 10 mais recentes)..."
            ls -1t data/results/result-*.json | tail -n +11 | while read file; do
                if [ $(stat -c %Y "$file") -lt $(date -d "$DAYS days ago" +%s) ]; then
                    rm "$file"
                fi
            done
        else
            echo "✅ Nenhum arquivo de resultado antigo encontrado"
        fi
    else
        echo "✅ Menos de 10 arquivos de resultado, mantendo todos"
    fi
fi

# Limpar logs temporários
echo "🗑️  Limpando logs temporários..."
rm -f /tmp/test-*.log 2>/dev/null || true

echo ""
echo "✅ Limpeza concluída!"
echo ""
echo "📊 Status atual:"
if [ -d "data/progress" ]; then
    PROGRESS_FILES=$(ls -1 data/progress/*.json 2>/dev/null | wc -l)
    echo "   📈 Arquivos de progresso: $PROGRESS_FILES"
fi

if [ -d "data/results" ]; then
    RESULT_FILES=$(ls -1 data/results/*.json 2>/dev/null | wc -l)
    echo "   📄 Arquivos de resultado: $RESULT_FILES"
fi

DISK_USAGE=$(du -sh data/ 2>/dev/null | cut -f1)
echo "   💾 Espaço utilizado: $DISK_USAGE"
