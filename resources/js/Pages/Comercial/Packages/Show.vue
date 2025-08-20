<template>
  <div class="min-h-screen">
    <!-- Header Section -->
    <div class="border-b border-cyan-500">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-6 md:flex md:items-center md:justify-between">
          <div class="flex-1 min-w-0">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                  <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <h1 class="text-2xl font-bold text-sky-700">Detalhes do Pacote</h1>
                <strong class="text-sm text-gray-500">Visualize as informações do pacote</strong>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6">
          <!-- Cabeçalho -->
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-medium text-gray-900">{{ package.name }}</h3>
            <div class="flex space-x-2">
              <Link
                :href="route('packages.edit', package.id)"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
              >
                Editar
              </Link>
              <Link
                :href="route('packages.index')"
                class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
              >
                Voltar
              </Link>
            </div>
          </div>

          <!-- Informações do Pacote -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Informações Básicas</h4>
              <dl class="mt-4 space-y-4">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Nome</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ package.name }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Categoria</dt>
                  <dd class="mt-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                      {{ package.category }}
                    </span>
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Preço</dt>
                  <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ package.formatted_price }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Status</dt>
                  <dd class="mt-1">
                    <span
                      :class="[
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                        package.status === 'active'
                          ? 'bg-green-100 text-green-800'
                          : 'bg-red-100 text-red-800'
                      ]"
                    >
                      {{ package.status === 'active' ? 'Ativo' : 'Inativo' }}
                    </span>
                  </dd>
                </div>
              </dl>
            </div>

            <div v-if="package.description">
              <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Descrição</h4>
              <div class="mt-4">
                <p class="text-sm text-gray-900">{{ package.description }}</p>
              </div>
            </div>
          </div>

          <!-- Serviços Incluídos -->
          <div class="mt-8">
            <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-4">Serviços Incluídos</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div
                v-for="service in package.services"
                :key="service.id"
                class="p-4 border border-gray-200 rounded-lg"
              >
                <div class="flex justify-between items-start">
                  <div class="flex-1">
                    <h5 class="text-sm font-medium text-gray-900">{{ service.name }}</h5>
                    <p class="text-xs text-gray-500">{{ service.category }}</p>
                  </div>
                  <span class="text-sm font-semibold text-gray-900">{{ service.formatted_price }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Resumo Financeiro -->
          <div class="mt-8 p-4 bg-gray-50 rounded-lg">
            <h4 class="text-sm font-medium text-gray-700 mb-4">Resumo Financeiro</h4>
            <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <dt class="text-sm font-medium text-gray-500">Total de serviços</dt>
                <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ package.services.length }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Valor total dos serviços</dt>
                <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ package.total_services_value }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Preço do pacote</dt>
                <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ package.formatted_price }}</dd>
              </div>
              <div v-if="package.has_discount" class="md:col-span-3">
                <dt class="text-sm font-medium text-green-600">Desconto aplicado</dt>
                <dd class="mt-1 text-sm text-green-600 font-semibold">{{ package.discount_percentage }}%</dd>
              </div>
            </dl>
          </div>

          <!-- Informações Adicionais -->
          <div class="mt-8">
            <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-4">Informações Adicionais</h4>
            <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <dt class="text-sm font-medium text-gray-500">ID</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ package.id }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Criado em</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(package.created_at) }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Atualizado em</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(package.updated_at) }}</dd>
              </div>
            </dl>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  package: Object
})

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script> 