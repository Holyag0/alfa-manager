#!/bin/bash

# Deploy Script para Digital Ocean
# Autor: Holyago

set -e

echo "🚀 Iniciando deploy da aplicação..."

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Verificar se .env.production existe
if [ ! -f .env.production ]; then
    echo -e "${RED}❌ Erro: Arquivo .env.production não encontrado${NC}"
    echo "Copie .env.production.example para .env.production e configure"
    exit 1
fi

echo -e "${YELLOW}📦 Parando containers antigos...${NC}"
docker-compose -f docker-compose.prod.yml down

echo -e "${YELLOW}🏗️  Construindo imagens Docker...${NC}"
docker-compose -f docker-compose.prod.yml build --no-cache

echo -e "${YELLOW}🔄 Copiando .env.production para .env...${NC}"
cp .env.production .env

echo -e "${YELLOW}🚀 Iniciando containers...${NC}"
docker-compose -f docker-compose.prod.yml up -d

echo -e "${YELLOW}⏳ Aguardando containers ficarem saudáveis...${NC}"
sleep 10

echo -e "${YELLOW}🔑 Gerando APP_KEY (se necessário)...${NC}"
docker-compose -f docker-compose.prod.yml exec -T app php artisan key:generate --force || true

echo -e "${YELLOW}📊 Executando migrations...${NC}"
docker-compose -f docker-compose.prod.yml exec -T app php artisan migrate --force

echo -e "${YELLOW}🔗 Criando link de storage...${NC}"
docker-compose -f docker-compose.prod.yml exec -T app php artisan storage:link || true

echo -e "${YELLOW}🗑️  Limpando caches...${NC}"
docker-compose -f docker-compose.prod.yml exec -T app php artisan cache:clear
docker-compose -f docker-compose.prod.yml exec -T app php artisan config:cache
docker-compose -f docker-compose.prod.yml exec -T app php artisan route:cache
docker-compose -f docker-compose.prod.yml exec -T app php artisan view:cache
docker-compose -f docker-compose.prod.yml exec -T app php artisan event:cache

echo -e "${YELLOW}🔄 Reiniciando queue workers...${NC}"
docker-compose -f docker-compose.prod.yml restart queue

echo -e "${YELLOW}🧹 Limpando imagens antigas...${NC}"
docker image prune -f

echo -e "${GREEN}✅ Deploy concluído com sucesso!${NC}"
echo ""
echo -e "${YELLOW}📊 Status dos containers:${NC}"
docker-compose -f docker-compose.prod.yml ps

echo ""
echo -e "${GREEN}🎉 Aplicação rodando em: http://localhost${NC}"
echo -e "${YELLOW}📝 Para ver logs: docker-compose -f docker-compose.prod.yml logs -f${NC}"