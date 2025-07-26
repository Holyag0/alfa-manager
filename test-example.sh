#!/bin/bash

echo "🧪 ALFA MANAGER - EXEMPLO DE EXECUÇÃO DE TESTES"
echo "================================================"

echo ""
echo "📋 1. PREPARANDO AMBIENTE DE TESTES..."
echo "   - Subindo containers de teste..."
npm run test:setup

echo ""
echo "🔧 2. EXECUTANDO UNIT TESTS..."
echo "   - Testando Services (EnrollmentService, ServiceGuardian)"
npm run test:unit

echo ""
echo "🌐 3. EXECUTANDO FEATURE TESTS..."
echo "   - Testando fluxos completos de matrícula"
npm run test:feature

echo ""
echo "📊 4. EXECUTANDO TODOS OS TESTES COM COBERTURA..."
npm run test:coverage

echo ""
echo "⚡ 5. EXECUTANDO TESTES EM PARALELO (mais rápido)..."
npm run test:parallel

echo ""
echo "🔍 6. EXECUTANDO TESTE ESPECÍFICO..."
npm run test:filter EnrollmentServiceTest

echo ""
echo "🧹 7. LIMPANDO AMBIENTE DE TESTES..."
npm run test:down

echo ""
echo "✅ TESTES CONCLUÍDOS!"
echo ""
echo "💡 DICAS:"
echo "   - Use 'npm run test' para execução rápida"
echo "   - Use 'npm run test:coverage' para ver cobertura de código"
echo "   - Use 'npm run test:parallel' para execução mais rápida"
echo "   - Use 'npm run test:filter NomeDoTeste' para teste específico"
echo ""
echo "📖 DOCUMENTAÇÃO:"
echo "   - Unit Tests: tests/Unit/"
echo "   - Feature Tests: tests/Feature/"
echo "   - Factories: database/factories/"
echo "   - Docker Config: docker-compose.test.yml" 