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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <h1 class="text-2xl font-bold text-sky-700">Detalhes do Cargo</h1>
                <strong class="text-sm text-gray-500">Visualize as informações do cargo</strong>
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
            <h3 class="text-lg font-medium text-gray-900">{{ position.name }}</h3>
            <div class="flex space-x-2">
              <Link
                :href="route('cadastros.positions.edit', position.id)"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
              >
                Editar
              </Link>
              <Link
                :href="route('cadastros.positions.index')"
                class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
              >
                Voltar
              </Link>
            </div>
          </div>

          <!-- Informações do Cargo -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Informações Básicas</h4>
              <dl class="mt-4 space-y-4">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Nome</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ position.name }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Status</dt>
                  <dd class="mt-1">
                    <span
                      :class="[
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                        position.is_active
                          ? 'bg-green-100 text-green-800'
                          : 'bg-red-100 text-red-800'
                      ]"
                    >
                      {{ position.is_active ? 'Ativo' : 'Inativo' }}
                    </span>
                  </dd>
                </div>
                <div v-if="position.description">
                  <dt class="text-sm font-medium text-gray-500">Descrição</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ position.description }}</dd>
                </div>
              </dl>
            </div>
            <div>
              <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Informações do Sistema</h4>
              <dl class="mt-4 space-y-4">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Criado em</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(position.created_at) }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Atualizado em</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(position.updated_at) }}</dd>
                </div>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  position: Object
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

