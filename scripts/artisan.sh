#!/bin/bash

# Script para executar comandos artisan no container
# Autor: Holyago
# Uso: ./scripts/artisan.sh migrate
#      ./scripts/artisan.sh db:seed
#      ./scripts/artisan.sh tinker

# Obter diretório do script e mudar para raiz do projeto
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
cd "$PROJECT_ROOT" || exit 1

if [ -z "$1" ]; then
    echo "❌ Erro: Comando não fornecido"
    echo "💡 Uso: ./scripts/artisan.sh <comando>"
    echo "   Exemplo: ./scripts/artisan.sh migrate"
    exit 1
fi

docker-compose -f docker-compose.prod.yml exec app php artisan "$@"

