#!/bin/bash

# Script para executar comandos artisan no container
# Autor: Holyago
# Uso: ./artisan.sh migrate
#      ./artisan.sh db:seed
#      ./artisan.sh tinker

if [ -z "$1" ]; then
    echo "❌ Erro: Comando não fornecido"
    echo "💡 Uso: ./artisan.sh <comando>"
    echo "   Exemplo: ./artisan.sh migrate"
    exit 1
fi

docker-compose -f docker-compose.prod.yml exec app php artisan "$@"