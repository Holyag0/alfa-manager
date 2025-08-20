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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <h1 class="text-2xl font-bold text-sky-700">Detalhes da Categoria</h1>
                <strong class="text-sm text-gray-500">Visualize as informações da categoria</strong>
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
            <h3 class="text-lg font-medium text-gray-900">{{ category.name }}</h3>
            <div class="flex space-x-2">
              <Link
                :href="route('categories.edit', category.id)"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
              >
                Editar
              </Link>
              <Link
                :href="route('categories.index')"
                class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
              >
                Voltar
              </Link>
            </div>
          </div>

          <!-- Informações da Categoria -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Informações Básicas</h4>
              <dl class="mt-4 space-y-4">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Nome</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ category.name }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Tipo</dt>
                  <dd class="mt-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                      {{ getTypeLabel(category.type) }}
                    </span>
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Status</dt>
                  <dd class="mt-1">
                    <span
                      :class="[
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                        category.is_active
                          ? 'bg-green-100 text-green-800'
                          : 'bg-red-100 text-red-800'
                      ]"
                    >
                      {{ category.is_active ? 'Ativo' : 'Inativo' }}
                    </span>
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Cor</dt>
                  <dd class="mt-1">
                    <div class="flex items-center">
                      <div
                        class="w-8 h-8 rounded-full border border-gray-300"
                        :style="{ backgroundColor: category.formatted_color }"
                      ></div>
                      <span class="ml-2 text-sm text-gray-900">{{ category.formatted_color }}</span>
                    </div>
                  </dd>
                </div>
              </dl>
            </div>

            <div v-if="category.description">
              <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Descrição</h4>
              <div class="mt-4">
                <p class="text-sm text-gray-900">{{ category.description }}</p>
              </div>
            </div>
          </div>

          <!-- Estatísticas -->
          <div class="mt-8 p-4 bg-gray-50 rounded-lg">
            <h4 class="text-sm font-medium text-gray-700 mb-4">Estatísticas</h4>
            <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <dt class="text-sm font-medium text-gray-500">Total de serviços</dt>
                <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ category.services_count || 0 }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Total de pacotes</dt>
                <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ category.packages_count || 0 }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Total de itens</dt>
                <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ (category.services_count || 0) + (category.packages_count || 0) }}</dd>
              </div>
            </dl>
          </div>

          <!-- Informações Adicionais -->
          <div class="mt-8">
            <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-4">Informações Adicionais</h4>
            <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <dt class="text-sm font-medium text-gray-500">ID</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ category.id }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Criado em</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(category.created_at) }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Atualizado em</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(category.updated_at) }}</dd>
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
  category: Object
})

const getTypeLabel = (type) => {
  const types = {
    'service': 'Apenas Serviços',
    'package': 'Apenas Pacotes',
    'both': 'Serviços e Pacotes'
  }
  return types[type] || type
}

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
