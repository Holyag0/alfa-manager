#!/bin/bash

# Obter diretório do script e mudar para raiz do projeto
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
cd "$PROJECT_ROOT" || exit 1

# Cores
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}📊 Status dos Containers${NC}"
echo "================================"
docker-compose -f docker-compose.prod.yml ps

echo ""
echo -e "${BLUE}💾 Uso de Recursos${NC}"
echo "================================"
docker stats --no-stream --format "table {{.Name}}\t{{.CPUPerc}}\t{{.MemUsage}}\t{{.NetIO}}" \
    $(docker-compose -f docker-compose.prod.yml ps -q)

echo ""
echo -e "${BLUE}🔍 Health Checks${NC}"
echo "================================"

check_health() {
    CONTAINER=$1
    STATUS=$(docker inspect --format='{{.State.Health.Status}}' "$CONTAINER" 2>/dev/null || echo "no-healthcheck")
    
    if [ "$STATUS" = "healthy" ]; then
        echo -e "${GREEN}✅ $CONTAINER: $STATUS${NC}"
    elif [ "$STATUS" = "unhealthy" ]; then
        echo -e "${RED}❌ $CONTAINER: $STATUS${NC}"
    elif [ "$STATUS" = "starting" ]; then
        echo -e "${YELLOW}⏳ $CONTAINER: $STATUS${NC}"
    else
        RUNNING=$(docker inspect --format='{{.State.Running}}' "$CONTAINER" 2>/dev/null)
        if [ "$RUNNING" = "true" ]; then
            echo -e "${GREEN}✅ $CONTAINER: running${NC}"
        else
            echo -e "${RED}❌ $CONTAINER: stopped${NC}"
        fi
    fi
}

check_health alfa-nginx
check_health alfa-app
check_health alfa-mysql
check_health alfa-redis
check_health alfa-queue
check_health alfa-scheduler

echo ""
echo -e "${BLUE}📝 Logs Recentes (últimas 10 linhas)${NC}"
echo "================================"
docker-compose -f docker-compose.prod.yml logs --tail=10

echo ""
echo -e "${BLUE}💡 Comandos úteis:${NC}"
echo "  - Ver logs: docker-compose -f docker-compose.prod.yml logs -f"
echo "  - Reiniciar: docker-compose -f docker-compose.prod.yml restart"
echo "  - Parar: docker-compose -f docker-compose.prod.yml down"

