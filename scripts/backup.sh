#!/bin/bash

# Backup Script para MySQL
# Autor: Holyago

set -e

# Obter diretório do script e mudar para raiz do projeto
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
cd "$PROJECT_ROOT" || exit 1

# Configurações
BACKUP_DIR="./backups"
DATE=$(date +%Y%m%d_%H%M%S)
CONTAINER="alfa-mysql"

# Carregar variáveis do .env.production
if [ -f .env.production ]; then
    export $(cat .env.production | grep -v '#' | grep -v '^$' | xargs)
fi

# Criar diretório de backups se não existir
mkdir -p "$BACKUP_DIR"

# Nome do arquivo de backup
BACKUP_FILE="$BACKUP_DIR/backup_${DB_DATABASE}_${DATE}.sql.gz"

echo "🗄️  Iniciando backup do banco de dados..."
echo "📦 Database: $DB_DATABASE"
echo "📁 Arquivo: $BACKUP_FILE"

# Executar backup
docker exec "$CONTAINER" mysqldump \
    -u"${DB_USERNAME}" \
    -p"${DB_PASSWORD}" \
    "${DB_DATABASE}" \
    | gzip > "$BACKUP_FILE"

if [ -f "$BACKUP_FILE" ]; then
    SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    echo "✅ Backup criado com sucesso!"
    echo "📊 Tamanho: $SIZE"
    
    # Manter apenas os últimos 7 backups
    echo "🧹 Limpando backups antigos (mantendo últimos 7)..."
    ls -t "$BACKUP_DIR"/backup_*.sql.gz 2>/dev/null | tail -n +8 | xargs -r rm -f
    
    echo "📋 Backups disponíveis:"
    ls -lh "$BACKUP_DIR"/backup_*.sql.gz 2>/dev/null || echo "Nenhum backup encontrado"
else
    echo "❌ Erro ao criar backup!"
    exit 1
fi

