#!/bin/bash

# Restore Script para MySQL
# Autor: Holyago

set -e

# Obter diretório do script e mudar para raiz do projeto
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
cd "$PROJECT_ROOT" || exit 1

BACKUP_DIR="./backups"
CONTAINER="alfa-mysql"

# Carregar variáveis do .env.production
if [ -f .env.production ]; then
    export $(cat .env.production | grep -v '#' | grep -v '^$' | xargs)
fi

# Verificar se foi passado um arquivo de backup
if [ -z "$1" ]; then
    echo "❌ Uso: ./scripts/restore.sh [arquivo_backup.sql.gz]"
    echo ""
    echo "📋 Backups disponíveis:"
    ls -lh "$BACKUP_DIR"/backup_*.sql.gz 2>/dev/null || echo "Nenhum backup encontrado"
    exit 1
fi

BACKUP_FILE=$1

# Verificar se o arquivo existe
if [ ! -f "$BACKUP_FILE" ]; then
    echo "❌ Arquivo não encontrado: $BACKUP_FILE"
    exit 1
fi

echo "⚠️  ATENÇÃO: Esta operação irá SOBRESCREVER o banco de dados atual!"
echo "📦 Database: $DB_DATABASE"
echo "📁 Backup: $BACKUP_FILE"
echo ""
read -p "Deseja continuar? (yes/no): " confirm

if [ "$confirm" != "yes" ]; then
    echo "❌ Operação cancelada"
    exit 0
fi

echo "🗄️  Iniciando restore do banco de dados..."

# Restaurar backup
gunzip < "$BACKUP_FILE" | docker exec -i "$CONTAINER" mysql \
    -u"${DB_USERNAME}" \
    -p"${DB_PASSWORD}" \
    "${DB_DATABASE}"

if [ $? -eq 0 ]; then
    echo "✅ Restore concluído com sucesso!"
    echo "🔄 Limpando caches..."
    docker-compose -f docker-compose.prod.yml exec -T app php artisan cache:clear
    docker-compose -f docker-compose.prod.yml exec -T app php artisan config:clear
    docker-compose -f docker-compose.prod.yml restart queue
    echo "✅ Caches limpos e queue reiniciada!"
else
    echo "❌ Erro ao restaurar backup!"
    exit 1
fi

